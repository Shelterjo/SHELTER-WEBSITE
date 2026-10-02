<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Core\DatabaseBackup;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PDO;
use Tests\TestCase;

/**
 * OPS-041: the Owner sees the last database backup — when, how big, and whether the last run worked — on Needs
 * attention, where a failed scheduled task already shows up.
 */
class LastBackupTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->dir = sys_get_temp_dir().'/shelter-last-backup-'.bin2hex(random_bytes(4));
        mkdir($this->dir);
        config(['backup.root' => $this->dir.'/backups']);
        (new PDO('sqlite:'.$this->dir.'/source.sqlite'))->exec('create table t (id integer primary key)');
        config(['database.connections.backup_source' => ['driver' => 'sqlite', 'database' => $this->dir.'/source.sqlite', 'prefix' => '']]);
        $this->actingAs(User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create());
    }

    /** Moves the clock and signs in again (the Owner session has an absolute lifetime). */
    private function at(string $time): void
    {
        $this->travelTo(CarbonImmutable::parse($time, 'Asia/Amman'));
        $this->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    private function line(): string
    {
        $html = (string) $this->get('/dashboard/attention')->assertOk()->getContent();
        if (preg_match('#<p class="ui-record__status" data-backup-state="([a-z]+)">(.*?)</p>#s', $html, $m) !== 1) {
            $this->fail('the "Last backup" line is not on the page');
        }

        return $m[1].' | '.trim((string) preg_replace('/\s+/u', ' ', strip_tags($m[2])));
    }

    public function test_the_line_follows_the_backups(): void
    {
        $this->at('2026-10-02 03:00:00');
        $this->assertSame('none | آخر نسخة احتياطية لقاعدة البيانات: لا توجد نسخة بعد', $this->line());

        $this->assertSame('success', app(DatabaseBackup::class)->run('scheduled', 'backup_source')->status);
        $this->assertMatchesRegularExpression('/^ok \| آخر نسخة احتياطية لقاعدة البيانات: 2026-10-02 03:00 · \d+(\.\d)? (B|KB) سليمة$/u', $this->line());

        // A day and a half without a new one: the scheduler may have stopped.
        $this->at('2026-10-03 15:01:00');
        $this->assertStringStartsWith('overdue | آخر نسخة احتياطية لقاعدة البيانات: 2026-10-02 03:00 ·', $this->line());
        $this->assertStringContainsString('متأخرة — لا نسخة منذ أكثر من 36 ساعة', $this->line());

        // The next attempt fails: the last good backup stays visible, with the failed attempt next to it.
        config(['database.connections.backup_source.database' => $this->dir.'/gone.sqlite']);
        $this->assertSame('failed', app(DatabaseBackup::class)->run('scheduled', 'backup_source')->status);
        $line = $this->line();
        $this->assertStringStartsWith('failed | آخر نسخة احتياطية لقاعدة البيانات: 2026-10-02 03:00 ·', $line);
        $this->assertStringEndsWith('فشلت آخر محاولة · 2026-10-03 15:01', $line);
    }

    public function test_english_parity(): void
    {
        config(['shelter.dashboard_locale' => 'en']);
        $this->at('2026-10-02 03:00:00');
        app(DatabaseBackup::class)->run('scheduled', 'backup_source');

        $this->assertMatchesRegularExpression('/^ok \| Last database backup: 2026-10-02 03:00 · \d+(\.\d)? (B|KB) OK$/', $this->line());
    }
}

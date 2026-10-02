<?php

namespace Tests\Feature\Dashboard;

use App\Enums\FactStatus;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Fact;
use App\Models\HoursException;
use App\Models\Market;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Dashboard\HoursEditor;
use App\Services\MasterData\MasterData;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/** Dashboard → Branches and hours (MDH-006/007, HOURS-005…012, BRANCH-010, M50): preview, reason, approval, priority. */
class HoursEditorTest extends TestCase
{
    use RefreshDatabase;

    private Branch $drive;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $this->drive = Branch::query()->where('code', 'BR-DRIVE')->firstOrFail();
    }

    /** @return array<int, array{open: string, opens: string, closes: string}> */
    private function week(string $opens = '08:00', string $closes = '01:00'): array
    {
        $days = [];
        foreach ([6, 0, 1, 2, 3, 4, 5] as $day) {
            $days[$day] = ['open' => '1', 'opens' => $opens, 'closes' => $closes];
        }

        return $days;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TestResponse<Response>
     */
    private function hours(array $data): TestResponse
    {
        return $this->post('/dashboard/data/branches/'.$this->drive->id.'/hours', $data);
    }

    private function open(CarbonImmutable $at): ?bool
    {
        $this->app->forgetScopedInstances();
        $market = Market::query()->firstOrFail();

        return app(MasterData::class)->openState($this->drive->refresh(), $market, $at)?->isOpen;
    }

    public function test_editing_needs_a_fresh_confirmation(): void
    {
        $this->get('/dashboard/data/branches')->assertOk()->assertSee('شلتر كوفي درايف');
        $this->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->subMinutes(30)->getTimestamp()]);
        $this->get('/dashboard/data/branches/'.$this->drive->id)->assertRedirect('/dashboard/confirm');
        $this->hours(['days' => $this->week(), 'action' => 'preview'])->assertRedirect('/dashboard/confirm');
    }

    public function test_hours_are_published_only_after_the_same_preview_and_with_a_reason(): void
    {
        $html = (string) $this->get('/dashboard/data/branches/'.$this->drive->id)->assertOk()->getContent();
        $this->assertStringContainsString('value="07:00"', $html, 'the current week is in the form');

        $days = $this->week();
        $days[5] = ['open' => '', 'opens' => '', 'closes' => '']; // closed on Fridays
        $preview = (string) $this->hours(['days' => $days, 'action' => 'preview'])->assertOk()->getContent();
        $this->assertStringContainsString('هكذا ستظهر للعملاء', $preview);
        $this->assertSame(['07:00'], $this->drive->hours()->where('weekday', 6)->pluck('opens_at')->map(fn ($t) => substr((string) $t, 0, 5))->all(), 'a preview saves nothing');
        preg_match('/name="previewed" value="([a-f0-9]{64})"/', $preview, $m);
        $fingerprint = $m[1] ?? '';

        $this->hours(['days' => $days, 'action' => 'publish', 'previewed' => $fingerprint, 'reason' => ''])->assertSee('اكتب سبب التغيير');
        $tampered = $days;
        $tampered[6]['closes'] = '03:00';
        $this->hours(['days' => $tampered, 'action' => 'publish', 'previewed' => $fingerprint, 'reason' => 'Winter'])->assertSee('تغيّرت الأوقات بعد المعاينة');

        $this->hours(['days' => $days, 'action' => 'publish', 'previewed' => $fingerprint, 'reason' => 'Winter hours'])
            ->assertRedirect('/dashboard/data/branches/'.$this->drive->id);
        $this->assertSame(6, $this->drive->hours()->count(), 'Friday closed → no row');
        $facts = Fact::query()->where('key', 'hours.BR-DRIVE.regular')->orderBy('id')->get();
        $this->assertSame([FactStatus::Superseded, FactStatus::Approved], $facts->pluck('status')->all());
        $this->assertSame('OWNER-DASHBOARD', $facts->last()?->decision_ref);
        $this->assertSame('Winter hours', AuditLog::query()->where('action', 'hours.regular_published')->firstOrFail()->meta['reason'] ?? null);

        // 08:00 → 01:00 the next morning counts for the day it started (HOURS-012); Friday is closed.
        $saturday = CarbonImmutable::parse('2026-10-03 00:30', 'Asia/Amman'); // Friday night → Saturday 00:30 (Friday closed)
        $this->assertFalse($this->open($saturday));
        $this->assertTrue($this->open(CarbonImmutable::parse('2026-10-04 00:30', 'Asia/Amman')), 'Saturday night after midnight');
        $this->assertFalse($this->open(CarbonImmutable::parse('2026-10-04 07:30', 'Asia/Amman')));
    }

    public function test_bad_times_are_refused(): void
    {
        $days = $this->week();
        $days[0] = ['open' => '1', 'opens' => '09:00', 'closes' => '09:00'];
        $days[1] = ['open' => '1', 'opens' => '25:00', 'closes' => '10:00'];
        $html = (string) $this->hours(['days' => $days, 'action' => 'preview'])->getContent();
        $this->assertStringContainsString('وقت الفتح والإغلاق متساويان', $html);
        $this->assertStringContainsString('href="#d1-opens"', $html);
        $this->assertStringNotContainsString('هكذا ستظهر للعملاء', $html, 'no preview while something is wrong');
    }

    public function test_exceptions_follow_their_priority_and_never_touch_the_regular_hours(): void
    {
        $url = '/dashboard/data/branches/'.$this->drive->id.'/exceptions';
        $monday = CarbonImmutable::parse('next monday 12:00', 'Asia/Amman');
        $this->assertTrue($this->open($monday));

        $special = ['kind' => 'special', 'starts_on' => $monday->toDateString(), 'ends_on' => $monday->addDays(2)->toDateString(), 'mode' => 'hours',
            'opens_at' => '18:00', 'closes_at' => '02:00', 'reason' => 'Ramadan', 'status' => 'published'];
        $this->post($url, $special)->assertSessionHasNoErrors();
        $this->assertFalse($this->open($monday), 'special hours replace the regular ones on those days');
        $this->assertTrue($this->open($monday->setTime(23, 0)));
        $this->assertTrue($this->open($monday->addDays(3)), 'regular hours come back by themselves');

        $this->post($url, ['reason' => 'x'] + $special)->assertSessionHasErrors(['starts_on'], null, 'exception');
        $emergency = ['kind' => 'emergency', 'starts_on' => $monday->toDateString(), 'ends_on' => $monday->toDateString(), 'reason' => 'Power cut', 'status' => 'published'];
        $this->post($url, $emergency)->assertSessionHasNoErrors();
        $this->assertFalse($this->open($monday->setTime(23, 0)), 'emergency beats special');

        $this->post($url, ['kind' => 'holiday', 'starts_on' => '2020-01-01', 'ends_on' => '2020-01-02', 'mode' => 'closed', 'reason' => 'Old', 'status' => 'published'])
            ->assertSessionHasErrors(['ends_on'], null, 'exception');
        $this->post($url, ['kind' => 'holiday', 'starts_on' => $monday->addDays(5)->toDateString(), 'ends_on' => $monday->addDays(4)->toDateString(), 'reason' => '', 'status' => 'draft'])
            ->assertSessionHasErrors(['ends_on', 'reason'], null, 'exception');

        $closure = HoursException::query()->where('kind', 'emergency')->firstOrFail();
        $this->post($url.'/'.$closure->id.'/archive');
        $this->assertTrue($this->open($monday->setTime(23, 0)), 'cancelled → the special hours apply again');
        $this->assertSame(FactStatus::Approved, Fact::query()->where('key', 'hours.BR-DRIVE.regular')->firstOrFail()->status, 'regular hours untouched');
        $this->assertSame(2, HoursException::query()->count(), 'nothing is deleted');
    }

    public function test_the_fingerprint_names_the_exact_week(): void
    {
        $a = HoursEditor::fingerprint($this->drive, [[6, '08:00', '01:00'], [0, '08:00', '01:00']]);
        $b = HoursEditor::fingerprint($this->drive, [[0, '08:00', '01:00'], [6, '08:00', '01:00']]);
        $this->assertSame($a, $b, 'order does not matter');
        $this->assertNotSame($a, HoursEditor::fingerprint($this->drive, [[6, '08:00', '02:00'], [0, '08:00', '01:00']]));
    }
}

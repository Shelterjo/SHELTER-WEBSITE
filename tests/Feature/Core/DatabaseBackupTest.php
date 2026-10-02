<?php

namespace Tests\Feature\Core;

use App\Models\ScheduledJobRun;
use App\Models\Signal;
use App\Services\Core\DatabaseBackup;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Testing\PendingCommand;
use PDO;
use Tests\TestCase;

/**
 * App-level database backup (DEPLOY-005, OPS-041): `php artisan ops:backup-db` writes one checked, gzipped copy into a
 * private folder, keeps the newest BACKUP_KEEP, and records every run (JobRuns: a failure raises the scheduler signal).
 * SQLite runs for real here; the MySQL path runs against a faked mysqldump to prove where the credentials go.
 */
class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    /** @var list<array{command: list<string>, options: string, mode: string, content: string}> what the faked mysqldump was given */
    private array $dumps = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir().'/shelter-backup-test-'.bin2hex(random_bytes(4));
        mkdir($this->dir);
        config(['backup.root' => $this->dir.'/backups', 'backup.keep' => 14]);
        // The live database stays the tests' in-memory one (job runs and signals); the backup reads a real file.
        $source = $this->dir.'/source.sqlite';
        (new PDO('sqlite:'.$source))->exec("create table menu (id integer primary key, name text); insert into menu (name) values ('قهوة'), ('شاي')");
        config(['database.connections.backup_source' => ['driver' => 'sqlite', 'database' => $source, 'prefix' => '']]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    /** @param array<string, string> $options */
    private function backup(array $options = []): PendingCommand
    {
        $command = $this->artisan('ops:backup-db', $options);
        $this->assertInstanceOf(PendingCommand::class, $command);

        return $command;
    }

    /** @return list<string> */
    private function backups(): array
    {
        $names = array_values(array_filter(scandir($this->dir.'/backups') ?: [], fn (string $n): bool => $n !== '.' && $n !== '..'));
        sort($names);

        return $names;
    }

    private function mode(string $path): string
    {
        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /** A fake mysqldump that records what it was given and writes $dump into --result-file (null = writes nothing). */
    private function fakeMysqldump(?string $dump, int $exitCode = 0, string $errorOutput = ''): void
    {
        Process::fake(function (PendingProcess $process) use ($dump, $exitCode, $errorOutput) {
            $command = is_array($process->command) ? array_values(array_map(strval(...), $process->command)) : [];
            $options = substr($command[1] ?? '', strlen('--defaults-extra-file='));
            $this->dumps[] = ['command' => $command, 'options' => $options, 'mode' => $this->mode($options), 'content' => (string) file_get_contents($options)];
            foreach ($command as $argument) {
                if ($dump !== null && str_starts_with($argument, '--result-file=')) {
                    file_put_contents(substr($argument, strlen('--result-file=')), $dump);
                }
            }

            return Process::result(errorOutput: $errorOutput, exitCode: $exitCode);
        });
    }

    public function test_a_sqlite_backup_is_compressed_private_and_restorable(): void
    {
        $this->backup(['--reason' => 'manual', '--database' => 'backup_source'])->assertSuccessful();

        $files = $this->backups();
        $this->assertCount(1, $files);
        $this->assertMatchesRegularExpression('/^db-\d{8}-\d{6}-manual-[a-z0-9]{6}\.sqlite\.gz$/', $files[0]);
        $file = $this->dir.'/backups/'.$files[0];
        $this->assertSame('0600', $this->mode($file), 'readable by the app user only');
        $this->assertSame('0700', $this->mode($this->dir.'/backups'));

        // Restorable: unzip it and read the rows back.
        $restored = $this->dir.'/restored.sqlite';
        file_put_contents($restored, gzdecode((string) file_get_contents($file)));
        $rows = (new PDO('sqlite:'.$restored))->query('select name from menu order by id');
        $this->assertSame(['قهوة', 'شاي'], $rows === false ? [] : $rows->fetchAll(PDO::FETCH_COLUMN));

        $run = ScheduledJobRun::query()->where('job', DatabaseBackup::JOB)->sole();
        $this->assertSame('success', $run->status);
        $this->assertStringContainsString($files[0], (string) $run->summary);
        $this->assertStringContainsString('sha256 '.hash_file('sha256', $file), (string) $run->summary, 'the checksum is recorded');
    }

    public function test_only_the_newest_backups_are_kept_and_other_files_are_never_touched(): void
    {
        config(['backup.keep' => 3]);
        mkdir($this->dir.'/backups', 0700);
        file_put_contents($this->dir.'/backups/notes.txt', 'not a backup');
        $start = CarbonImmutable::parse('2026-10-01 03:00:00', 'Asia/Amman');
        foreach (range(0, 4) as $day) {
            $this->travelTo($start->addDays($day));
            $this->backup(['--reason' => 'scheduled', '--database' => 'backup_source'])->assertSuccessful();
        }

        $kept = array_values(array_filter($this->backups(), fn (string $n): bool => str_starts_with($n, 'db-')));
        $this->assertCount(3, $kept);
        $this->assertStringStartsWith('db-20261003-000000-', $kept[0], 'the three newest (UTC in the name)');
        $this->assertStringStartsWith('db-20261005-000000-', $kept[2]);
        $this->assertContains('notes.txt', $this->backups());
        $this->assertStringContainsString('1 old removed', (string) ScheduledJobRun::query()->latest('id')->value('summary'));
    }

    public function test_work_files_left_by_a_killed_run_are_removed_once_no_run_can_still_use_them(): void
    {
        mkdir($this->dir.'/backups', 0700);
        $stale = $this->dir.'/backups/.db-20261001-000000-scheduled-abcdef.sql.gz.options.tmp';
        $recent = $this->dir.'/backups/.db-20261002-000000-manual-ghijkl.sql.gz.partial';
        touch($stale, now()->subSeconds(2 * 1800 + 60)->getTimestamp());
        touch($recent, now()->subMinutes(5)->getTimestamp());

        $this->backup(['--database' => 'backup_source'])->assertSuccessful();

        $this->assertFileDoesNotExist($stale, 'older than twice the dump timeout: no run can still be using it');
        $this->assertFileExists($recent, 'may belong to a run still going');
    }

    public function test_a_failure_is_recorded_signalled_and_leaves_nothing_behind(): void
    {
        config(['database.connections.backup_source.database' => $this->dir.'/missing.sqlite']);

        $this->backup(['--database' => 'backup_source'])->assertFailed();

        $run = ScheduledJobRun::query()->where('job', DatabaseBackup::JOB)->sole();
        $this->assertSame('failed', $run->status);
        $this->assertStringContainsString('SQLite database file was not found', (string) $run->error);
        $this->assertSame(1, Signal::query()->open()->where('dedupe_key', 'job:ops:backup-db:failed')->count());
        $this->assertSame([], $this->backups(), 'no half-written file');
        $this->assertFileDoesNotExist($this->dir.'/missing.sqlite', 'the backup never creates the database it reads');

        // The next good backup closes the issue.
        config(['database.connections.backup_source.database' => $this->dir.'/source.sqlite']);
        $this->backup(['--database' => 'backup_source'])->assertSuccessful();
        $this->assertSame(0, Signal::query()->open()->where('dedupe_key', 'job:ops:backup-db:failed')->count());
    }

    public function test_the_folder_is_never_under_public_nor_relative(): void
    {
        foreach ([public_path('backups-test'), public_path('media/../backups-test'), 'storage/backups'] as $root) {
            config(['backup.root' => $root]);
            $this->backup(['--database' => 'backup_source'])->assertFailed();
        }
        $this->assertDirectoryDoesNotExist(public_path('backups-test'));
        $this->assertSame(3, ScheduledJobRun::query()->where('status', 'failed')->count());
    }

    public function test_a_mistyped_reason_is_refused_without_recording_a_failure(): void
    {
        $this->backup(['--reason' => 'Pre Deploy!'])->assertExitCode(2);

        $this->assertSame(0, ScheduledJobRun::query()->count());
        $this->assertSame(0, Signal::query()->count());
    }

    public function test_mysql_credentials_go_through_a_private_option_file_never_the_command_line(): void
    {
        config(['database.connections.backup_mysql' => [
            'driver' => 'mariadb', 'host' => '127.0.0.1', 'port' => '3306', 'database' => 'shelter',
            'username' => 'shelter_app', 'password' => 'p@ss "w#rd\\ x', 'unix_socket' => '',
        ]]);
        $this->fakeMysqldump("CREATE TABLE `menu` (`id` int);\n-- Dump completed on 2026-10-02  3:00:00\n");

        $this->backup(['--reason' => 'pre-deploy', '--database' => 'backup_mysql'])->assertSuccessful();

        $this->assertCount(1, $this->dumps);
        ['command' => $command, 'options' => $options] = $this->dumps[0];
        $this->assertSame('mysqldump', $command[0]);
        $this->assertStringStartsWith('--defaults-extra-file='.$this->dir.'/backups/', $command[1], 'first option; inside the private folder');
        $this->assertSame('shelter', $command[count($command) - 1]);
        foreach ($command as $argument) {
            $this->assertStringNotContainsString('p@ss', $argument, 'no password on the command line');
            $this->assertStringNotContainsString('shelter_app', $argument);
        }
        $this->assertSame('0600', $this->dumps[0]['mode']);
        $this->assertStringContainsString("[client]\nuser=\"shelter_app\"\npassword=\"p@ss \\\"w#rd\\\\ x\"\nhost=\"127.0.0.1\"\nport=3306\n", $this->dumps[0]['content']);
        $this->assertFileDoesNotExist($options, 'deleted right after the dump');
        $this->assertCount(1, $this->backups(), 'only the backup is left in the folder');
        $this->assertMatchesRegularExpression('/^db-\d{8}-\d{6}-pre-deploy-[a-z0-9]{6}\.sql\.gz$/', $this->backups()[0]);
    }

    public function test_a_failed_or_incomplete_mysql_dump_does_not_count(): void
    {
        config(['database.connections.backup_mysql' => ['driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 3306, 'database' => 'shelter', 'username' => 'u', 'password' => 'secret-value']]);

        $this->fakeMysqldump(null, 2, "mysqldump: Got error: 1045: Access denied for user 'u'@'localhost' (using password: YES)");
        $this->backup(['--database' => 'backup_mysql'])->assertFailed();
        $this->fakeMysqldump("CREATE TABLE `menu` (`id` int);\n"); // exits 0, but no "Dump completed" line
        $this->backup(['--database' => 'backup_mysql'])->assertFailed();

        $errors = array_map(strval(...), ScheduledJobRun::query()->orderBy('id')->pluck('error')->all());
        $this->assertStringContainsString('Access denied', $errors[0]);
        $this->assertStringContainsString('incomplete', $errors[1]);
        $this->assertStringNotContainsString('secret-value', implode(' ', $errors));
        foreach ($this->dumps as $dump) {
            $this->assertFileDoesNotExist($dump['options'], 'the option file is deleted even when the dump fails');
        }
        $this->assertSame([], $this->backups());
    }

    public function test_it_runs_every_night_before_the_other_night_jobs(): void
    {
        $events = [];
        foreach (app(Schedule::class)->events() as $event) {
            $events[(string) $event->description] = $event;
        }
        $backup = $events[DatabaseBackup::JOB] ?? null;
        $this->assertInstanceOf(Event::class, $backup);
        $this->assertSame('0 3 * * *', $backup->expression);
        $this->assertSame('Asia/Amman', $backup->timezone);
        $this->assertTrue($backup->withoutOverlapping);
        foreach (['facts:expire-verified', 'monitors:daily'] as $later) {
            $event = $events[$later] ?? null;
            $this->assertInstanceOf(Event::class, $event);
            $this->assertSame('Asia/Amman', $event->timezone);
            [$minute, $hour] = explode(' ', $event->expression);
            $this->assertSame('3', $hour);
            $this->assertGreaterThan(0, (int) $minute, "{$later} runs after the 03:00 backup");
        }
    }
}

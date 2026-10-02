<?php

namespace App\Services\Core;

use App\Models\ScheduledJobRun;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\ConfigurationUrlParser;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;

/**
 * Database backups made by the app (DEPLOY-005, OPS-041): `php artisan ops:backup-db`, daily at 03:00 Amman and before
 * every deploy. MySQL/MariaDB through mysqldump in one consistent transaction — the credentials go through a temporary
 * 0600 option file in the backup folder, deleted right after: never on the command line, where `ps` would show them.
 * SQLite through VACUUM INTO, a consistent copy even while the site writes. A backup only counts once it is checked (the
 * dump is complete, or the copy passes integrity_check) and its .gz reads back whole. Backups live in a PRIVATE folder
 * (0700, files 0600, never under public/) and only the newest `backup.keep` stay. Every run is recorded by JobRuns: the
 * Owner sees the last backup on "Needs attention", and a failed run raises the scheduler's HIGH signal until the next
 * success.
 */
final class DatabaseBackup
{
    public const JOB = 'ops:backup-db';

    /** Why the backup was made (part of the file name): manual, scheduled, pre-deploy… */
    public const REASON = '/^[a-z0-9][a-z0-9-]{0,19}$/';

    /** A daily backup older than this shows as overdue (the scheduler may have stopped). */
    public const OVERDUE_HOURS = 36;

    /** db-<UTC yyyymmdd-hhmmss>-<reason>-<random>.<sql|sqlite>.gz: only files named like this are listed or pruned. */
    private const NAME = '/^db-(\d{8}-\d{6})-[a-z0-9-]{1,20}-[a-z0-9]{6}\.(sql|sqlite)\.gz$/';

    /**
     * Work files of a run (hidden, never listed: the plain dump, the .gz being written, the option file). A killed run can
     * leave one behind; the next run deletes it once it is older than any run can last (twice the dump timeout).
     */
    private const WORK = '/^\.db-.+\.(tmp|partial)$/';

    public function __construct(private readonly JobRuns $runs) {}

    /** One recorded run: create, then prune. The run keeps the file name, size and SHA-256 (DISASTER-RECOVERY DR-T1). */
    public function run(string $reason, ?string $connection = null): ScheduledJobRun
    {
        return $this->runs->run(self::JOB, function () use ($reason, $connection): string {
            $file = $this->create($reason, $connection);
            $pruned = $this->prune();

            return basename($file).' · '.(int) filesize($file).' bytes · sha256 '.hash_file('sha256', $file).' · '.$pruned.' old removed';
        });
    }

    /** Writes one checked, compressed backup and returns its path. */
    public function create(string $reason, ?string $connection = null): string
    {
        if (preg_match(self::REASON, $reason) !== 1) {
            throw new RuntimeException('The reason must be 1–20 lowercase letters, digits or dashes.');
        }
        $connection ??= (string) config('database.default');
        $config = config("database.connections.{$connection}");
        if (! is_array($config)) {
            throw new RuntimeException("Unknown database connection [{$connection}].");
        }
        /** @var array<string, mixed> $config */
        $config = (new ConfigurationUrlParser)->parseConfiguration($config);
        $driver = is_string($config['driver'] ?? null) ? $config['driver'] : '';
        $kind = match ($driver) {
            'mysql', 'mariadb' => 'sql',
            'sqlite' => 'sqlite',
            default => throw new RuntimeException("No backup method for the [{$driver}] driver."),
        };

        $directory = $this->directory(create: true);
        $name = 'db-'.now()->utc()->format('Ymd-His')."-{$reason}-".Str::lower(Str::random(6)).".{$kind}.gz";
        $plain = "{$directory}/.{$name}.tmp";
        $partial = "{$directory}/.{$name}.partial";
        $options = "{$directory}/.{$name}.options.tmp";
        try {
            if ($kind === 'sql') {
                $this->dumpMysql($config, $plain, $options);
            } else {
                $this->copySqlite($config, $plain);
            }
            $this->gzip($plain, $partial);
            if (! rename($partial, "{$directory}/{$name}")) {
                throw new RuntimeException('The finished backup could not be moved into place.');
            }
        } finally {
            foreach ([$plain, $partial, $options] as $work) {
                if (is_file($work)) {
                    unlink($work);
                }
            }
        }

        return "{$directory}/{$name}";
    }

    /** Keeps the newest `backup.keep` backups and returns how many older ones it deleted. Other files are never touched. */
    public function prune(): int
    {
        $directory = $this->directory();
        if (! is_dir($directory)) {
            return 0;
        }
        $deleted = 0;
        foreach (array_slice($this->names($directory), max(1, (int) config('backup.keep', 14))) as $name) {
            $deleted += unlink("{$directory}/{$name}") ? 1 : 0;
        }
        foreach (scandir($directory) ?: [] as $name) {
            if (preg_match(self::WORK, $name) === 1 && (int) filemtime("{$directory}/{$name}") < now()->subSeconds(2 * (int) config('backup.timeout', 1800))->getTimestamp()) {
                unlink("{$directory}/{$name}");
            }
        }

        return $deleted;
    }

    /** @return array{name: string, bytes: int, at: CarbonImmutable}|null the newest backup on disk */
    public function latest(): ?array
    {
        $directory = $this->directory();
        $name = is_dir($directory) ? ($this->names($directory)[0] ?? null) : null;
        if ($name === null || preg_match(self::NAME, $name, $m) !== 1) {
            return null;
        }
        $at = CarbonImmutable::createFromFormat('Ymd-His', $m[1], 'UTC');

        return $at instanceof CarbonImmutable ? ['name' => $name, 'bytes' => (int) filesize("{$directory}/{$name}"), 'at' => $at] : null;
    }

    /**
     * The "Last backup" line on Needs attention: the newest backup on disk (time, size) and how the last run went.
     *
     * @return array{state: 'ok'|'overdue'|'failed'|'none', latest: array{name: string, bytes: int, at: CarbonImmutable}|null, failed_at: CarbonInterface|null}
     */
    public function status(): array
    {
        try {
            $latest = $this->latest();
        } catch (RuntimeException) {
            $latest = null; // a refused folder: every run fails too, so the line shows the failure
        }
        $run = ScheduledJobRun::query()->where('job', self::JOB)->whereIn('status', ['success', 'failed'])
            ->orderByDesc('started_at')->orderByDesc('id')->first();
        $failed = $run !== null && $run->status === 'failed';
        $state = match (true) {
            $failed => 'failed',
            $latest === null => 'none',
            $latest['at']->lessThan(now()->subHours(self::OVERDUE_HOURS)) => 'overdue',
            default => 'ok',
        };

        return ['state' => $state, 'latest' => $latest, 'failed_at' => $failed ? ($run->finished_at ?? $run->started_at) : null];
    }

    /** The private backup folder. Refuses a relative path, or one inside public/ (the web server would serve it). */
    public function directory(bool $create = false): string
    {
        $root = rtrim((string) config('backup.root'), '/');
        $public = rtrim(public_path(), '/');
        if (! str_starts_with($root, '/')) {
            throw new RuntimeException('BACKUP_ROOT must be an absolute path.');
        }
        if ($this->inside($this->normalize($root), $this->normalize($public))) {
            throw new RuntimeException('BACKUP_ROOT must not be inside public/.');
        }
        if ($create && ! is_dir($root) && ! mkdir($root, 0700, true) && ! is_dir($root)) {
            throw new RuntimeException('The backup folder could not be created.');
        }
        $real = realpath($root);
        $realPublic = realpath($public);
        if ($real !== false && $realPublic !== false && $this->inside($real, $realPublic)) {
            throw new RuntimeException('BACKUP_ROOT must not lead into public/.'); // e.g. through a symlink
        }

        return $real !== false ? $real : $root;
    }

    /** @return list<string> backup file names, newest first (the name starts with the UTC time) */
    private function names(string $directory): array
    {
        $names = array_values(array_filter(scandir($directory) ?: [], fn (string $n): bool => preg_match(self::NAME, $n) === 1));
        rsort($names, SORT_STRING);

        return $names;
    }

    /** @param array<string, mixed> $config */
    private function dumpMysql(array $config, string $target, string $options): void
    {
        $database = is_string($config['database'] ?? null) ? $config['database'] : '';
        if (preg_match('/^[A-Za-z0-9_][A-Za-z0-9_$-]*$/', $database) !== 1) {
            throw new RuntimeException('The database name is missing or unusual; it is not passed to mysqldump.');
        }
        $this->privateFile($target);
        $this->optionFile($config, $options);
        try {
            // --defaults-extra-file must come first. One transaction = a consistent copy without locking the site.
            $result = Process::timeout((int) config('backup.timeout', 1800))->run([
                (string) config('backup.mysqldump', 'mysqldump'),
                '--defaults-extra-file='.$options,
                '--single-transaction',
                '--quick',
                '--no-tablespaces',
                '--hex-blob',
                '--default-character-set=utf8mb4',
                '--result-file='.$target,
                $database,
            ]);
        } finally {
            unlink($options);
        }
        if ($result->failed()) {
            throw new RuntimeException('mysqldump failed (exit '.$result->exitCode().'): '.Str::limit(trim($result->errorOutput()), 300));
        }
        if (! str_contains($this->tail($target), '-- Dump completed')) {
            throw new RuntimeException('The dump is incomplete (no "Dump completed" line).');
        }
    }

    /**
     * MySQL option file with the connection's credentials: inside the private backup folder (not the shared /tmp),
     * made 0600 before the password is written. Values are quoted and escaped the way the client library reads them,
     * so a password with quotes, # or spaces stays intact.
     *
     * @param  array<string, mixed>  $config
     */
    private function optionFile(array $config, string $path): void
    {
        $this->privateFile($path);
        $value = fn (mixed $v): string => '"'.addcslashes(is_scalar($v) ? (string) $v : '', "\\\"\n\r\t").'"';
        $lines = ['[client]', 'user='.$value($config['username'] ?? ''), 'password='.$value($config['password'] ?? '')];
        if (is_string($config['host'] ?? null) && $config['host'] !== '') {
            $lines[] = 'host='.$value($config['host']);
        }
        if (is_numeric($config['port'] ?? null)) {
            $lines[] = 'port='.(int) $config['port'];
        }
        if (is_string($config['unix_socket'] ?? null) && $config['unix_socket'] !== '') {
            $lines[] = 'socket='.$value($config['unix_socket']);
        }
        if (file_put_contents($path, implode("\n", $lines)."\n") === false) {
            throw new RuntimeException('The temporary option file could not be written.');
        }
    }

    /** @param array<string, mixed> $config */
    private function copySqlite(array $config, string $target): void
    {
        $database = is_string($config['database'] ?? null) ? $config['database'] : '';
        $source = $database === '' || $database === ':memory:' || str_contains($database, 'mode=memory') ? false : realpath($database);
        if ($source === false || ! is_file($source)) {
            throw new RuntimeException('The SQLite database file was not found (an in-memory database has nothing to back up).');
        }
        // Read-only: the backup can never change the live file. VACUUM INTO writes one consistent copy.
        $read = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::SQLITE_ATTR_OPEN_FLAGS => PDO::SQLITE_OPEN_READONLY];
        $mask = umask(0077); // the copy is created private, not made private afterwards
        try {
            $pdo = new PDO('sqlite:'.$source, null, null, $read);
            $pdo->exec('PRAGMA busy_timeout = 10000');
            $pdo->prepare('VACUUM INTO ?')->execute([$target]);
            unset($pdo);
        } finally {
            umask($mask);
        }

        $check = (new PDO('sqlite:'.$target, null, null, $read))->query('PRAGMA integrity_check');
        if ($check === false || $check->fetchColumn() !== 'ok') {
            throw new RuntimeException('The SQLite copy failed its integrity check.');
        }
    }

    /** Compresses into a 0600 file, then reads the archive back: it must give exactly the original bytes count. */
    private function gzip(string $source, string $target): void
    {
        $this->privateFile($target);
        $in = fopen($source, 'rb');
        $out = gzopen($target, 'wb6');
        if ($in === false || $out === false) {
            throw new RuntimeException('The backup could not be compressed.');
        }
        try {
            while (! feof($in)) {
                $chunk = fread($in, 1 << 20);
                if ($chunk === false) {
                    throw new RuntimeException('Reading the backup failed.');
                }
                if ($chunk !== '' && gzwrite($out, $chunk) !== strlen($chunk)) {
                    throw new RuntimeException('Writing the compressed backup failed (disk full?).');
                }
            }
        } finally {
            fclose($in);
            $closed = gzclose($out);
        }
        if (! $closed) {
            throw new RuntimeException('Writing the compressed backup failed (disk full?).');
        }

        $read = 0;
        $archive = gzopen($target, 'rb');
        if ($archive === false) {
            throw new RuntimeException('The compressed backup does not open.');
        }
        while (! gzeof($archive)) {
            $data = gzread($archive, 1 << 20);
            if ($data === false) {
                gzclose($archive);
                throw new RuntimeException('The compressed backup does not read back.');
            }
            $read += strlen($data);
        }
        gzclose($archive);
        if ($read !== filesize($source)) {
            throw new RuntimeException('The compressed backup does not read back whole.');
        }
    }

    /** Creates an empty file readable by this user only, before anything is written into it. */
    private function privateFile(string $path): void
    {
        if (! touch($path) || ! chmod($path, 0600)) {
            throw new RuntimeException('A backup file could not be created.');
        }
    }

    /** The last bytes of a file (mysqldump ends a complete dump with a "-- Dump completed" comment). */
    private function tail(string $path): string
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return '';
        }
        if (fseek($handle, -256, SEEK_END) !== 0) {
            rewind($handle);
        }
        $tail = stream_get_contents($handle);
        fclose($handle);

        return $tail === false ? '' : $tail;
    }

    /** Resolves "." and ".." without touching the disk (the folder may not exist yet). */
    private function normalize(string $path): string
    {
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '..') {
                array_pop($parts);
            } elseif ($part !== '' && $part !== '.') {
                $parts[] = $part;
            }
        }

        return '/'.implode('/', $parts);
    }

    private function inside(string $path, string $parent): bool
    {
        return str_starts_with($path.'/', $parent.'/');
    }
}

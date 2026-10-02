<?php

namespace App\Console\Commands;

use App\Services\Core\DatabaseBackup;
use Illuminate\Console\Command;

/**
 * Database backup (DEPLOY-005, OPS-041): one checked, gzipped copy in the private backup folder (BACKUP_ROOT), keeping
 * the newest BACKUP_KEEP. Runs daily from the scheduler and as step 4 of every deploy (docs/platform/DEPLOYMENT.md §5),
 * where a non-zero exit stops the deploy. Every run is recorded (JobRuns) and shown on Needs attention.
 */
final class OpsBackupDb extends Command
{
    protected $signature = 'ops:backup-db
        {--reason=manual : Why: manual, scheduled, pre-deploy… (part of the file name)}
        {--database= : Connection to back up (default: the app\'s own)}';

    protected $description = 'Back up the database (gzip, private folder, newest BACKUP_KEEP kept)';

    public function handle(DatabaseBackup $backup): int
    {
        $reason = (string) $this->option('reason');
        if (preg_match(DatabaseBackup::REASON, $reason) !== 1) {
            $this->error('ops:backup-db: --reason takes 1–20 lowercase letters, digits or dashes.');

            return self::INVALID; // a typo is not a failed backup: nothing is recorded
        }
        $database = $this->option('database');
        $run = $backup->run($reason, is_string($database) && $database !== '' ? $database : null);
        if ($run->status !== 'success') {
            $this->error('ops:backup-db: FAILED — '.$run->error);

            return self::FAILURE;
        }
        $this->info('ops:backup-db: '.$run->summary);

        return self::SUCCESS;
    }
}

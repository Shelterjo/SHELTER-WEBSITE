<?php

use App\Services\Core\Attention;
use App\Services\Core\DatabaseBackup;
use App\Services\Core\JobRuns;
use App\Services\MasterData\FactRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
| Scheduled jobs. Every job runs through JobRuns so its health is visible (M35 §49).
| Time-dependent public state (open now, active campaign) is computed at request time; jobs only do housekeeping.
| Server cron: * * * * * php artisan schedule:run
*/

// Database backup (DEPLOY-005, OPS-041) first, before the night's jobs change anything. DatabaseBackup records its own
// run through JobRuns, so `php artisan ops:backup-db --reason=pre-deploy` during a deploy shows up the same way.
Schedule::call(fn () => app(DatabaseBackup::class)->run('scheduled'))
    ->name(DatabaseBackup::JOB)
    ->timezone('Asia/Amman')
    ->dailyAt('03:00')
    ->withoutOverlapping();

Schedule::call(fn () => app(JobRuns::class)->run('facts:expire-verified', fn (): string => app(FactRegistry::class)->expireVerified().' expired'))
    ->name('facts:expire-verified')
    ->timezone('Asia/Amman')
    ->dailyAt('03:10')
    ->withoutOverlapping();

// Unsubmitted careers uploads (drafts, never applications) older than 24 hours (RECRUITMENT-SECURITY §2.5).
Schedule::call(fn () => app(JobRuns::class)->run('careers:prune-drafts', function (): string {
    Artisan::call('careers:prune-drafts');

    return trim(Artisan::output());
}))
    ->name('careers:prune-drafts')
    ->hourly()
    ->withoutOverlapping();

// Needs attention (MON-007): the daily monitors (image rights ending within 30 days, questions Shaltoor keeps leaving
// unanswered) raise or close their issues; Shaltoor's questions older than 90 days are deleted in the same run.
Schedule::call(fn () => app(JobRuns::class)->run('monitors:daily', fn (): string => app(Attention::class)->runMonitors()))
    ->name('monitors:daily')
    ->timezone('Asia/Amman')
    ->dailyAt('03:20')
    ->withoutOverlapping();

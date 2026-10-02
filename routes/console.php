<?php

use App\Services\Core\Attention;
use App\Services\Core\JobRuns;
use App\Services\MasterData\FactRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
| Scheduled jobs. Every job runs through JobRuns so its health is visible (M35 §49).
| Time-dependent public state (open now, active campaign) is computed at request time; jobs only do housekeeping.
| Server cron: * * * * * php artisan schedule:run
*/

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

// Needs attention (MON-007): the daily monitors (image rights ending within 30 days, …) raise or close their issues.
Schedule::call(fn () => app(JobRuns::class)->run('monitors:daily', fn (): string => app(Attention::class)->runMonitors()))
    ->name('monitors:daily')
    ->timezone('Asia/Amman')
    ->dailyAt('03:20')
    ->withoutOverlapping();

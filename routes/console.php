<?php

use App\Services\Core\JobRuns;
use App\Services\MasterData\FactRegistry;
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

<?php

namespace App\Services\Core;

use App\Enums\Priority;
use App\Enums\Severity;
use App\Enums\SignalCategory;
use App\Enums\SignalKind;
use App\Models\ScheduledJobRun;
use Closure;
use Throwable;

/**
 * Wraps a scheduled job so every run is recorded (last run / failures shown in Site Health). A failure raises
 * one HIGH OPERATIONS signal per job; the next success resolves it. The exception message is truncated and
 * never contains payload data (jobs carry IDs only).
 */
final class JobRuns
{
    public function __construct(private readonly Signals $signals) {}

    /** @param Closure(): (string|null) $job returns an optional one-line summary */
    public function run(string $name, Closure $job): ScheduledJobRun
    {
        $started = now();
        $run = ScheduledJobRun::query()->create(['job' => $name, 'started_at' => $started, 'status' => 'running']);
        try {
            $summary = $job();
            $run->forceFill(['status' => 'success', 'summary' => $summary === null ? null : mb_substr($summary, 0, 500)]);
            $this->signals->resolve("job:{$name}:failed");
        } catch (Throwable $e) {
            $run->forceFill(['status' => 'failed', 'error' => mb_substr($e::class.': '.$e->getMessage(), 0, 500)]);
            $this->signals->raise(
                SignalKind::Issue, SignalCategory::Operations, Severity::High, Priority::ActionRequired, 'scheduler',
                "تعذّر تشغيل مهمة مجدولة: {$name}", "A scheduled task failed: {$name}",
                dedupeKey: "job:{$name}:failed", details: ['job' => $name],
            );
        }
        $run->forceFill(['finished_at' => now(), 'duration_ms' => (int) $started->diffInMilliseconds(now())])->save();

        return $run;
    }
}

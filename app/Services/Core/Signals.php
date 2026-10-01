<?php

namespace App\Services\Core;

use App\Enums\Priority;
use App\Enums\Severity;
use App\Enums\SignalCategory;
use App\Enums\SignalKind;
use App\Enums\SignalStatus;
use App\Models\Signal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The one signals source (NOTIFICATIONS.md, INCIDENTS.md). Monitors, facts, sync and forms raise signals here;
 * the dashboard shows EVENTs as notifications and open ISSUEs as "needs attention". Deduplicated by dedupe_key.
 */
final class Signals
{
    private const SEVERITY_RANK = ['INFO' => 0, 'LOW' => 1, 'MEDIUM' => 2, 'HIGH' => 3, 'CRITICAL' => 4];

    /**
     * @param  array<string, mixed>  $details
     */
    public function raise(
        SignalKind $kind,
        SignalCategory $category,
        Severity $severity,
        Priority $priority,
        string $source,
        string $titleAr,
        string $titleEn,
        ?string $dedupeKey = null,
        ?Model $subject = null,
        array $details = [],
        ?string $bodyAr = null,
        ?string $bodyEn = null,
        ?string $recommendedAction = null,
    ): Signal {
        return DB::transaction(function () use ($kind, $category, $severity, $priority, $source, $titleAr, $titleEn, $dedupeKey, $subject, $details, $bodyAr, $bodyEn, $recommendedAction): Signal {
            if ($dedupeKey !== null) {
                $open = Signal::query()->open()->where('dedupe_key', $dedupeKey)->lockForUpdate()->first();
                if ($open !== null) {
                    $open->occurrences++;
                    $open->last_seen_at = now();
                    if (self::SEVERITY_RANK[$severity->value] > self::SEVERITY_RANK[$open->severity->value]) {
                        $open->severity = $severity;
                        $open->priority = $priority;
                    }
                    $open->save();

                    return $open;
                }
            }

            $signal = new Signal([
                'kind' => $kind,
                'category' => $category,
                'severity' => $severity,
                'priority' => $priority,
                'status' => SignalStatus::Open,
                'dedupe_key' => $dedupeKey,
                'title_ar' => $titleAr,
                'title_en' => $titleEn,
                'body_ar' => $bodyAr,
                'body_en' => $bodyEn,
                'recommended_action' => $recommendedAction,
                'source' => $source,
                'details' => $details === [] ? null : AuditLogger::mask($details),
                'last_seen_at' => now(),
            ]);
            if ($subject !== null) {
                $signal->subject()->associate($subject);
            }
            $signal->save();

            return $signal;
        });
    }

    /** Resolves every open signal with this dedupe key (e.g. when a fact becomes publishable). */
    public function resolve(string $dedupeKey, ?User $by = null): int
    {
        return Signal::query()->open()->where('dedupe_key', $dedupeKey)->update([
            'status' => SignalStatus::Resolved->value,
            'resolved_at' => now(),
            'resolved_by' => $by?->id,
        ]);
    }

    /**
     * Dismiss is allowed for INFORMATION only. A critical or action-required item is never hidden (M35 §8).
     */
    public function dismiss(Signal $signal, User $by): bool
    {
        if ($signal->priority !== Priority::Information) {
            return false;
        }
        $signal->forceFill(['status' => SignalStatus::Dismissed, 'resolved_at' => now(), 'resolved_by' => $by->id])->save();

        return true;
    }
}

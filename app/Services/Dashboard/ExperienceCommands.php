<?php

namespace App\Services\Dashboard;

use App\Models\Experience;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Core\Versions;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * The commands every dynamic experience shares (DYNAMIC-EXPERIENCE-ENGINE §5, DX-013, DX-020, DX-032): pause / resume,
 * cancel, end now, archive / restore, and "Disable now" (the emergency switch of the Active Now screen). Each one
 * applies at once, keeps a version and an audit entry; nothing is deleted.
 */
final class ExperienceCommands
{
    public const COMMANDS = ['pause', 'resume', 'cancel', 'end', 'archive', 'restore', 'disable'];

    public function __construct(private readonly Versions $versions, private readonly AuditLogger $audit) {}

    /** Published: `scheduled` until it starts, `active` once it has (the site computes the state by time). */
    public static function timeStatus(?CarbonImmutable $starts, CarbonImmutable $now): string
    {
        return $starts !== null && $starts->greaterThan($now) ? 'scheduled' : 'active';
    }

    /** Runs one command; false when it does not apply to the experience's current state. `$area` names it in the audit. */
    public function run(Experience $experience, string $command, User $owner, string $area, ?CarbonImmutable $now = null): bool
    {
        $now ??= CarbonImmutable::now();
        $public = in_array($experience->status, ['scheduled', 'active'], true);
        $live = $experience->archived_at === null;
        $starts = $experience->starts_at === null ? null : CarbonImmutable::instance($experience->starts_at);
        $stopped = $experience->status === 'paused' || $experience->emergency_disabled || $experience->manual_state === 'off';
        $changes = match (true) {
            $command === 'pause' && $public && $live => ['status' => 'paused'],
            $command === 'resume' && $stopped && $live => [
                'status' => $experience->status === 'paused' ? self::timeStatus($starts, $now) : $experience->status,
                'emergency_disabled' => false, 'manual_state' => null,
            ],
            $command === 'cancel' && ($public || $experience->status === 'paused') && $live => ['status' => 'cancelled'],
            // Ending early keeps an event's page (marked ended) so shared links still work.
            $command === 'end' && $public && $starts !== null && $starts->lessThan($now) && $experience->ends_at !== null && $experience->ends_at->greaterThan($now) => ['status' => 'ended', 'ends_at' => $now->utc()],
            $command === 'archive' && $live => ['archived_at' => $now->utc()],
            $command === 'restore' && ! $live => ['archived_at' => null],
            $command === 'disable' && $public && $live && ! $experience->emergency_disabled => ['emergency_disabled' => true],
            default => null,
        };
        if ($changes === null) {
            return false;
        }

        DB::transaction(function () use ($experience, $command, $changes, $owner, $area): void {
            $before = self::snapshot($experience);
            $experience->forceFill($changes)->save();
            $after = self::snapshot($experience);
            $this->versions->record($experience, $experience->status, $after, 'command: '.$command, $owner);
            $this->audit->record($area.'.'.$command, $experience, ['before' => array_intersect_key($before, $changes), 'after' => array_intersect_key($after, $changes)], actor: $owner);
        });

        return true;
    }

    /** @return array<string, mixed> */
    public static function snapshot(Experience $experience): array
    {
        return [
            'type' => $experience->type, 'slug' => $experience->slug, 'status' => $experience->status,
            'title_ar' => $experience->title_ar, 'title_en' => $experience->title_en, 'body_ar' => $experience->body_ar, 'body_en' => $experience->body_en,
            'terms_ar' => $experience->terms_ar, 'terms_en' => $experience->terms_en,
            'cta_label_ar' => $experience->cta_label_ar, 'cta_label_en' => $experience->cta_label_en, 'cta_url' => $experience->cta_url,
            'placements' => $experience->placements, 'priority' => $experience->priority, 'branch_ids' => $experience->branch_ids,
            'details' => $experience->details,
            'starts_at' => $experience->starts_at?->toIso8601String(), 'ends_at' => $experience->ends_at?->toIso8601String(),
            'emergency_disabled' => $experience->emergency_disabled, 'archived_at' => $experience->archived_at?->toIso8601String(),
        ];
    }
}

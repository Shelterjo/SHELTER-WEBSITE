<?php

namespace App\Services\Dashboard;

use App\Models\Experience;
use App\Models\Market;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Core\Versions;
use App\Services\Dashboard\Concerns\ReadsExperienceInput;
use App\Services\Experiences\Placements;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * The Owner's announcements and campaigns (no code — M50, DX-010/011/013/018/021, DYNAMIC-EXPERIENCE-ENGINE §5): one
 * form — the kind (announcement, urgent notice, campaign/offer), where it shows (the top bar on every page or the home
 * feature — one place, never all at once), the text in both languages, an optional button, when (Amman time) and the
 * priority (automatic by kind, or the Owner's own). Publishing needs the title in both languages, a place and an end
 * that has not passed. Saving tells the Owner what else competes for the same place at the same time (DX-021). Every
 * save keeps a version and an audit entry; the commands (pause, resume, cancel, end, archive, disable) are shared.
 */
final class AnnouncementEditor
{
    use ReadsExperienceInput;

    public const TYPES = ['announcement', 'campaign'];

    /** Priority levels the Owner may choose (auto = by kind — Placements::DEFAULT_PRIORITY). */
    public const LEVELS = ['auto' => 0, 'urgent' => 100, 'high' => 70, 'normal' => 40, 'low' => 10];

    private const LIMITS = ['title' => 120, 'body' => 200, 'cta_label' => 40];

    public function __construct(private readonly Versions $versions, private readonly AuditLogger $audit, private readonly Placements $placements) {}

    /**
     * Where it stands for visitors now: live (showing) · outranked (live but another one has the place) · upcoming ·
     * draft · paused · cancelled · ended · incomplete · archived.
     */
    public function state(Experience $item, Market $market, ?CarbonImmutable $now = null): string
    {
        $now ??= CarbonImmutable::now();
        $state = match (true) {
            $item->archived_at !== null => 'archived',
            $item->status === 'draft' => 'draft',
            $item->status === 'cancelled' => 'cancelled',
            $item->status === 'paused' || $item->emergency_disabled || $item->manual_state === 'off' => 'paused',
            $item->status === 'ended' || ($item->ends_at !== null && ! $item->ends_at->greaterThan($now)) => 'ended',
            $item->starts_at !== null && $item->starts_at->greaterThan($now) => 'upcoming',
            default => null,
        };
        if ($state !== null) {
            return $state;
        }
        $live = $this->placements->live($market, $now);
        if (! $live->contains('id', $item->id)) {
            return 'incomplete';
        }
        $placement = ($item->placements ?? [])[0] ?? null;
        $winner = $live->first(fn (Experience $e): bool => in_array($placement, $e->placements ?? [], true));

        return $winner?->id === $item->id ? 'live' : 'outranked';
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{item: Experience|null, errors: array<string, string>, warnings: list<string>}
     */
    public function save(?Experience $item, array $input, Market $market, User $owner, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $errors = [];
        $type = in_array($input['type'] ?? null, self::TYPES, true) ? (string) $input['type'] : 'announcement';
        $values = [];
        foreach (['title', 'body', 'cta_label'] as $field) {
            foreach (['ar', 'en'] as $locale) {
                $value = self::text($input[$field.'_'.$locale] ?? null);
                if ($value !== null && mb_strlen($value) > self::LIMITS[$field]) {
                    $errors[$field.'_'.$locale] = (string) __('dashboard.pages.errors.too_long', ['max' => self::LIMITS[$field]]);
                }
                $values[$field.'_'.$locale] = $value;
            }
        }
        $placement = in_array($input['placement'] ?? null, Placements::ALL, true) ? (string) $input['placement'] : null;
        $timezone = $market->timezone !== '' ? $market->timezone : 'Asia/Amman';
        $starts = self::moment($input['starts_date'] ?? null, $input['starts_time'] ?? null, $timezone, 'starts', $errors);
        $ends = self::moment($input['ends_date'] ?? null, $input['ends_time'] ?? null, $timezone, 'ends', $errors);
        if ($starts !== null && $ends !== null && ! $ends->greaterThan($starts)) {
            $errors['ends_date'] = (string) __('dashboard.events.errors.order');
        }
        $url = is_string($input['cta_url'] ?? null) ? trim($input['cta_url']) : '';
        if ($url !== '' && ! self::validLink($url)) {
            $errors['cta_url'] = (string) __('dashboard.events.errors.url');
        }
        $level = is_string($input['level'] ?? null) && array_key_exists($input['level'], self::LEVELS) ? $input['level'] : 'auto';
        $urgent = $type === 'announcement' && ! empty($input['urgent']);

        $publish = ($input['status'] ?? '') === 'published';
        if ($publish) {
            foreach (['title_ar', 'title_en'] as $field) {
                if ($values[$field] === null) {
                    $errors[$field] ??= (string) __('dashboard.announcements.errors.required', ['field' => __('dashboard.announcements.fields.'.$field)]);
                }
            }
            if ($placement === null) {
                $errors['placement'] = (string) __('dashboard.announcements.errors.placement');
            }
            foreach (['starts_date' => $starts, 'ends_date' => $ends] as $field => $moment) {
                if ($moment === null) {
                    $errors[$field] ??= (string) __('dashboard.announcements.errors.required', ['field' => __('dashboard.announcements.fields.'.$field)]);
                }
            }
            if ($ends !== null && ! $ends->greaterThan($now)) {
                $errors['ends_date'] ??= (string) __('dashboard.events.errors.past');
            }
            if (($values['body_ar'] === null) !== ($values['body_en'] === null)) {
                $errors[$values['body_ar'] === null ? 'body_ar' : 'body_en'] ??= (string) __('dashboard.announcements.errors.both_languages');
            }
            $cta = [$values['cta_label_ar'] !== null, $values['cta_label_en'] !== null, $url !== ''];
            if (in_array(true, $cta, true) && in_array(false, $cta, true)) {
                $errors[! $cta[0] ? 'cta_label_ar' : (! $cta[1] ? 'cta_label_en' : 'cta_url')] ??= (string) __('dashboard.events.errors.cta');
            }
        }
        if ($errors !== []) {
            return ['item' => $item, 'errors' => $errors, 'warnings' => []];
        }

        $saved = DB::transaction(function () use ($item, $type, $values, $placement, $starts, $ends, $url, $level, $urgent, $publish, $market, $input, $owner, $now): Experience {
            $item ??= new Experience(['origin' => 'owner', 'market_id' => $market->id, 'timezone' => $market->timezone !== '' ? $market->timezone : 'Asia/Amman']);
            $created = ! $item->exists;
            $details = $item->details ?? [];
            $details['urgent'] = $urgent;
            $item->forceFill($values + [
                'type' => $type,
                'cta_url' => $url === '' ? null : $url,
                'placements' => $placement === null ? null : [$placement],
                'priority' => self::LEVELS[$level],
                'starts_at' => $starts?->utc(), 'ends_at' => $ends?->utc(),
                'status' => ! $publish ? 'draft' : ($item->status === 'paused' ? 'paused' : ExperienceCommands::timeStatus($starts, $now)),
                'details' => $details,
            ])->save();
            $reason = self::text($input['reason'] ?? null);
            $snapshot = ExperienceCommands::snapshot($item);
            $this->versions->record($item, $item->status, $snapshot, $reason === null ? null : mb_substr($reason, 0, 300), $owner);
            $this->audit->record($created ? 'announcements.created' : 'announcements.saved', $item,
                ['after' => array_intersect_key($snapshot, array_flip(['type', 'status', 'placements', 'starts_at', 'ends_at', 'priority']))], actor: $owner);

            return $item;
        });

        return ['item' => $saved, 'errors' => [], 'warnings' => $publish ? $this->conflicts($saved, $market) : []];
    }

    /**
     * What else wants the same place at an overlapping time, and who wins (DX-021) — in words for the Owner.
     *
     * @return list<string>
     */
    public function conflicts(Experience $item, Market $market): array
    {
        $placement = ($item->placements ?? [])[0] ?? null;
        if ($placement === null || $item->starts_at === null || $item->ends_at === null) {
            return [];
        }
        $timezone = $market->timezone !== '' ? $market->timezone : 'Asia/Amman';
        $mine = Placements::priority($item);
        $ar = app()->getLocale() === 'ar';
        $warnings = [];
        $others = Experience::query()->whereKeyNot($item->id)->whereIn('type', Placements::TYPES)->whereIn('status', ['scheduled', 'active'])
            ->whereNull('archived_at')->where('emergency_disabled', false)->whereNotNull('placements')
            ->where('starts_at', '<', $item->ends_at)->where('ends_at', '>', $item->starts_at)->get()
            ->filter(fn (Experience $e): bool => in_array($placement, $e->placements ?? [], true));
        foreach ($others as $other) {
            $from = CarbonImmutable::instance(max($item->starts_at, $other->starts_at ?? $item->starts_at))->setTimezone($timezone)->format('Y-m-d H:i');
            $to = CarbonImmutable::instance(min($item->ends_at, $other->ends_at ?? $item->ends_at))->setTimezone($timezone)->format('Y-m-d H:i');
            $name = ($ar ? $other->title_ar : $other->title_en) ?? $other->title_ar ?? '#'.$other->id;
            $theirs = Placements::priority($other);
            $key = $theirs > $mine ? 'loses' : ($theirs < $mine ? 'wins' : 'tie');
            $warnings[] = (string) __('dashboard.announcements.conflict.'.$key, ['name' => $name, 'from' => $from, 'to' => $to]);
        }

        return $warnings;
    }
}

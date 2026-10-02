<?php

namespace App\Services\Dashboard;

use App\Models\Branch;
use App\Models\Experience;
use App\Models\Market;
use App\Models\Media;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Core\Versions;
use App\Services\Dashboard\Concerns\ReadsExperienceInput;
use App\Services\Experiences\Events;
use App\Services\Media\MediaRights;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The Owner's events (no code — M50, DX-014, DYNAMIC-EXPERIENCE-ENGINE §5). One form: the text in both languages, when
 * (Amman time), where (our branches or another venue), an optional button, and the terms. A draft may be incomplete;
 * publishing needs the title and the description in both languages and dates that have not passed (LANGUAGE-PARITY,
 * G-21) — publishing is the Owner's approval of the text. Then the commands: pause / resume, cancel, end now, archive.
 * Nothing is deleted. Every save keeps a full version (content_versions) and an audit entry. The web address stays
 * fixed once the event has been published so shared links keep working.
 */
final class EventEditor
{
    use ReadsExperienceInput;

    /** Field => max length. */
    private const LIMITS = ['title' => 200, 'body' => 3000, 'terms' => 2000, 'cta_label' => 60, 'venue' => 200, 'cta_url' => 500, 'reason' => 300];

    public const COMMANDS = ExperienceCommands::COMMANDS;

    public function __construct(private readonly Versions $versions, private readonly AuditLogger $audit, private readonly Events $events, private readonly ExperienceCommands $commands) {}

    /**
     * Where the event stands for visitors right now, in one word for the dashboard.
     *
     * live | upcoming | draft | paused | cancelled | ended | incomplete | archived
     */
    public function state(Experience $event, Market $market, ?CarbonImmutable $now = null): string
    {
        $now ??= CarbonImmutable::now();

        return match (true) {
            $event->archived_at !== null => 'archived',
            $event->status === 'draft' => 'draft',
            $event->status === 'cancelled' => 'cancelled',
            $event->status === 'paused' || $event->emergency_disabled || $event->manual_state === 'off' => 'paused',
            $event->status === 'ended' || ($event->ends_at !== null && ! $event->ends_at->greaterThan($now)) => 'ended',
            $this->events->find($market, (string) $event->slug, 'ar', $now) === null => 'incomplete',
            $event->starts_at !== null && $event->starts_at->lessThanOrEqualTo($now) => 'live',
            default => 'upcoming',
        };
    }

    /** True once the event has been published at least once — its web address is then fixed. */
    public function addressFixed(Experience $event): bool
    {
        return $event->exists && is_string(($event->details ?? [])['first_published_at'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{event: Experience|null, errors: array<string, string>}
     */
    public function save(?Experience $event, array $input, Market $market, User $owner, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $values = self::texts($input);
        $errors = [];
        foreach ($values as $key => $value) {
            $limit = self::LIMITS[(string) preg_replace('/_(ar|en)$/', '', $key)];
            if ($value !== null && mb_strlen($value) > $limit) {
                $errors[$key] = (string) __('dashboard.pages.errors.too_long', ['max' => $limit]);
            }
        }

        $timezone = $market->timezone !== '' ? $market->timezone : 'Asia/Amman';
        $starts = self::moment($input['starts_date'] ?? null, $input['starts_time'] ?? null, $timezone, 'starts', $errors);
        $ends = self::moment($input['ends_date'] ?? null, $input['ends_time'] ?? null, $timezone, 'ends', $errors);
        if ($starts !== null && $ends !== null && ! $ends->greaterThan($starts)) {
            $errors['ends_date'] = (string) __('dashboard.events.errors.order');
        }

        $mediaId = is_numeric($input['media_id'] ?? null) ? (int) $input['media_id'] : null;
        if ($mediaId !== null && ! MediaRights::canUse(Media::query()->find($mediaId))) {
            $errors['media_id'] = (string) __('dashboard.awards.errors.image');
        }
        $url = is_string($input['cta_url'] ?? null) ? trim($input['cta_url']) : '';
        if ($url !== '' && ! self::validLink($url)) {
            $errors['cta_url'] = (string) __('dashboard.events.errors.url');
        }

        $active = Branch::query()->whereNull('archived_at')->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $branchIds = array_values(array_unique(array_map('intval', array_filter(is_array($input['branches'] ?? null) ? $input['branches'] : [], 'is_numeric'))));
        if (array_diff($branchIds, $active) !== []) {
            $errors['branches'] = (string) __('dashboard.events.errors.branch');
        }

        $fixed = $event !== null && $this->addressFixed($event);
        $slug = $fixed ? (string) $event->slug : self::text($input['slug'] ?? null);
        if (! $fixed && $slug !== null && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
            $errors['slug'] = (string) __('dashboard.events.errors.slug');
            $slug = null;
        }
        if (! $fixed && $slug !== null && $this->slugTaken($slug, $market, $event)) {
            $errors['slug'] = (string) __('dashboard.events.errors.slug_taken');
        }

        $publish = ($input['status'] ?? '') === 'published';
        if ($publish) {
            foreach (['title_ar', 'title_en', 'body_ar', 'body_en'] as $field) {
                if ($values[$field] === null) {
                    $errors[$field] ??= (string) __('dashboard.events.errors.required', ['field' => self::label($field)]);
                }
            }
            foreach (['starts_date', 'ends_date'] as $field) {
                if (($field === 'starts_date' ? $starts : $ends) === null) {
                    $errors[$field] ??= (string) __('dashboard.events.errors.required', ['field' => self::label($field)]);
                }
            }
            if ($ends !== null && ! $ends->greaterThan($now)) {
                $errors['ends_date'] ??= (string) __('dashboard.events.errors.past');
            }
            // A pair is shown only in both languages (LANGUAGE-PARITY): fill both or leave both empty.
            foreach (['terms', 'venue'] as $field) {
                if (($values[$field.'_ar'] === null) !== ($values[$field.'_en'] === null)) {
                    $empty = $values[$field.'_ar'] === null ? $field.'_ar' : $field.'_en';
                    $errors[$empty] ??= (string) __('dashboard.events.errors.both_languages', ['field' => self::label($empty)]);
                }
            }
            // The button: both labels and the link, or nothing.
            $cta = [$values['cta_label_ar'] !== null, $values['cta_label_en'] !== null, $url !== ''];
            if (in_array(true, $cta, true) && in_array(false, $cta, true)) {
                $missing = ! $cta[0] ? 'cta_label_ar' : (! $cta[1] ? 'cta_label_en' : 'cta_url');
                $errors[$missing] ??= (string) __('dashboard.events.errors.cta');
            }
        }
        if ($errors !== []) {
            return ['event' => $event, 'errors' => $errors];
        }

        $saved = DB::transaction(function () use ($event, $values, $starts, $ends, $url, $branchIds, $mediaId, $slug, $publish, $market, $input, $owner, $now): Experience {
            $event ??= new Experience(['type' => 'event', 'origin' => 'owner', 'market_id' => $market->id, 'timezone' => $market->timezone !== '' ? $market->timezone : 'Asia/Amman']);
            $created = ! $event->exists;
            $before = $created ? [] : self::snapshot($event);
            $details = $event->details ?? [];
            $details['venue_ar'] = $values['venue_ar'];
            $details['venue_en'] = $values['venue_en'];
            if ($slug === null && $publish) {
                $slug = $this->freeSlug((string) $values['title_en'], $market, $event);
            }
            // A paused event stays paused until resumed; otherwise published = scheduled or active by time.
            $status = ! $publish ? 'draft' : ($event->status === 'paused' ? 'paused' : ExperienceCommands::timeStatus($starts, $now));
            if ($publish && ! is_string($details['first_published_at'] ?? null)) {
                $details['first_published_at'] = $now->utc()->toIso8601String();
            }
            $event->forceFill([
                'slug' => $slug,
                'title_ar' => $values['title_ar'], 'title_en' => $values['title_en'],
                'body_ar' => $values['body_ar'], 'body_en' => $values['body_en'],
                'terms_ar' => $values['terms_ar'], 'terms_en' => $values['terms_en'],
                'cta_label_ar' => $values['cta_label_ar'], 'cta_label_en' => $values['cta_label_en'], 'cta_url' => $url === '' ? null : $url,
                'branch_ids' => $branchIds === [] ? null : $branchIds,
                'media_id' => $mediaId,
                'starts_at' => $starts?->utc(), 'ends_at' => $ends?->utc(),
                'status' => $status,
                'details' => $details,
            ])->save();
            $reason = self::text($input['reason'] ?? null);
            $reason = $reason === null ? null : mb_substr($reason, 0, self::LIMITS['reason']);
            $this->versions->record($event, $status, self::snapshot($event), $reason, $owner);
            $this->audit->record($created ? 'events.created' : 'events.saved', $event,
                ['before' => array_intersect_key($before, array_flip(['status', 'starts_at', 'ends_at'])), 'after' => array_intersect_key(self::snapshot($event), array_flip(['status', 'starts_at', 'ends_at']))],
                $reason === null ? [] : ['reason' => $reason], actor: $owner);

            return $event;
        });

        return ['event' => $saved, 'errors' => []];
    }

    /** Runs one command (pause, resume, cancel, end now, archive, restore, disable); false when it does not apply now. */
    public function command(Experience $event, string $command, User $owner, ?CarbonImmutable $now = null): bool
    {
        return $this->commands->run($event, $command, $owner, 'events', $now);
    }

    private function slugTaken(string $slug, Market $market, ?Experience $event): bool
    {
        return Experience::query()->where('slug', $slug)->where('market_id', $market->id)
            ->when($event?->exists, fn ($q) => $q->whereKeyNot($event?->id))->exists();
    }

    /** A free web address from the English title (event-2, event-3… when taken). */
    private function freeSlug(string $title, Market $market, Experience $event): string
    {
        $base = Str::limit(Str::slug($title), 70, '') ?: 'event';
        $base = trim($base, '-') ?: 'event';
        $slug = $base;
        for ($n = 2; $this->slugTaken($slug, $market, $event); $n++) {
            $slug = $base.'-'.$n;
        }

        return $slug;
    }

    /** @return array<string, mixed> */
    private static function snapshot(Experience $event): array
    {
        return [
            'slug' => $event->slug, 'status' => $event->status,
            'title_ar' => $event->title_ar, 'title_en' => $event->title_en, 'body_ar' => $event->body_ar, 'body_en' => $event->body_en,
            'terms_ar' => $event->terms_ar, 'terms_en' => $event->terms_en,
            'cta_label_ar' => $event->cta_label_ar, 'cta_label_en' => $event->cta_label_en, 'cta_url' => $event->cta_url,
            'branch_ids' => $event->branch_ids, 'venue_ar' => ($event->details ?? [])['venue_ar'] ?? null, 'venue_en' => ($event->details ?? [])['venue_en'] ?? null,
            'starts_at' => $event->starts_at?->toIso8601String(), 'ends_at' => $event->ends_at?->toIso8601String(),
            'media_id' => $event->media_id,
            'archived' => $event->archived_at !== null,
        ];
    }

    /**
     * The text fields in both languages, trimmed (empty = null).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string|null>
     */
    private static function texts(array $input): array
    {
        $values = [];
        foreach (['title', 'body', 'terms', 'cta_label', 'venue'] as $field) {
            foreach (['ar', 'en'] as $locale) {
                $values[$field.'_'.$locale] = self::text($input[$field.'_'.$locale] ?? null);
            }
        }

        return $values;
    }

    /** The field's name as the form shows it — so a message in the summary says which field it means. */
    private static function label(string $key): string
    {
        if (preg_match('/^(.+)_(ar|en)$/', $key, $m) === 1) {
            return __('dashboard.events.fields.'.$m[1]).' ('.__('dashboard.pages.'.($m[2] === 'ar' ? 'arabic' : 'english')).')';
        }

        return (string) __('dashboard.events.fields.'.$key);
    }
}

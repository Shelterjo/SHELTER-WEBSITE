<?php

namespace App\Services\Dashboard;

use App\Models\Experience;
use App\Models\Market;
use App\Models\Media;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Content\ContentGuard;
use App\Services\Core\AuditLogger;
use App\Services\Core\Versions;
use App\Services\Experiences\Recognitions;
use App\Services\Media\MediaRights;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * The Owner's Employee of the Month (no code — DX-007…009, M66 §40): one form — the person (a SHELTER Family profile),
 * the month and year, the title in both languages, a short recognition text, the photo, where it shows (home ·
 * SHELTER Family · Media Center) and draft or published. Its life (DX-008): draft → scheduled (a coming month) →
 * active (its month) → expired (after it, kept as history) → archived; nothing is deleted. Publishing needs the person
 * on SHELTER Family with their recorded consent, a photo the website may use, both titles, a place and a month that
 * has not ended — and one published recognition per month. The site checks the same again on every request
 * (Recognitions), so a withdrawn consent or photo hides it at once. Every save keeps a version and an audit entry with
 * ids and field names only — never a person's name.
 */
final class RecognitionEditor
{
    private const LIMITS = ['title' => 120, 'body' => 300];

    public function __construct(
        private readonly Versions $versions,
        private readonly AuditLogger $audit,
        private readonly Recognitions $recognitions,
    ) {}

    /** draft · scheduled · active (on the site now) · not_shown (its month, but something is missing) · expired · archived */
    public function state(Experience $item, ?CarbonImmutable $now = null): string
    {
        $now ??= CarbonImmutable::now();

        return match (true) {
            $item->archived_at !== null => 'archived',
            $item->status === 'draft' => 'draft',
            in_array($item->status, ['cancelled', 'ended'], true) || ($item->ends_at !== null && ! $item->ends_at->greaterThan($now)) => 'expired',
            $item->starts_at !== null && $item->starts_at->greaterThan($now) => 'scheduled',
            $this->reasons($item) !== [] => 'not_shown',
            default => 'active',
        };
    }

    /**
     * Why a published recognition is not on the site in its month, in the Owner's words (keys of recognition.reasons).
     *
     * @return list<string>
     */
    public function reasons(Experience $item): array
    {
        $reasons = $this->recognitions->problems($item);
        if ($item->status === 'paused' || $item->emergency_disabled || $item->manual_state === 'off') {
            $reasons[] = 'stopped';
        }

        return $reasons;
    }

    /**
     * Profiles that may be picked (id => name in the dashboard language); archived ones only when already chosen.
     *
     * @return array<int, string>
     */
    public function members(string $locale, ?int $keep = null): array
    {
        $options = [];
        foreach (TeamMember::query()->where(fn ($q) => $q->whereNull('archived_at')->when($keep !== null, fn ($k) => $k->orWhere('id', $keep)))
            ->orderBy('sort_order')->orderBy('id')->get() as $member) {
            $name = ($locale === 'ar' ? $member->display_name_ar : $member->display_name_en) ?? $member->display_name_ar ?? $member->display_name_en;
            $options[$member->id] = $name ?? '#'.$member->id;
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{item: Experience|null, errors: array<string, string>}
     */
    public function save(?Experience $item, array $input, Market $market, User $owner, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $errors = [];
        $values = [];
        foreach (['title', 'body'] as $field) {
            foreach (['ar', 'en'] as $locale) {
                $value = self::text($input[$field.'_'.$locale] ?? null);
                if ($value !== null && mb_strlen($value) > self::LIMITS[$field]) {
                    $errors[$field.'_'.$locale] = (string) __('dashboard.pages.errors.too_long', ['max' => self::LIMITS[$field]]);
                }
                $values[$field.'_'.$locale] = $value;
            }
        }
        $memberId = is_numeric($input['team_member_id'] ?? null) ? (int) $input['team_member_id'] : null;
        if ($memberId !== null && ! array_key_exists($memberId, $this->members('ar', $item === null ? null : Recognitions::memberId($item)))) {
            $memberId = null;
        }
        $month = $this->month($input['month'] ?? null, $input['year'] ?? null, $errors);
        $photo = is_numeric($input['media_id'] ?? null) ? (int) $input['media_id'] : null;
        if ($photo !== null && ! MediaRights::canUse(Media::query()->find($photo))) {
            $errors['media_id'] = (string) __('dashboard.recognition.errors.photo');
        }
        $placements = array_values(array_intersect(Recognitions::PLACEMENTS, is_array($input['placements'] ?? null) ? $input['placements'] : []));

        $publish = ($input['status'] ?? '') === 'published';
        if ($publish) {
            $this->checkPublish($item, $values, $memberId, $month, $photo, $placements, $now, $errors);
        }
        if ($errors !== []) {
            return ['item' => $item, 'errors' => $errors];
        }

        $saved = DB::transaction(function () use ($item, $values, $memberId, $month, $photo, $placements, $publish, $market, $input, $owner, $now): Experience {
            $item ??= new Experience(['type' => Recognitions::TYPE, 'origin' => 'owner', 'market_id' => $market->id, 'timezone' => 'Asia/Amman']);
            $created = ! $item->exists;
            $starts = $month;
            $details = $item->details ?? [];
            $details['team_member_id'] = $memberId;
            $details['month'] = $month?->format('Y-m');
            $item->forceFill($values + [
                'media_id' => $photo,
                'placements' => $placements === [] ? null : $placements,
                'starts_at' => $starts?->utc(),
                'ends_at' => $starts?->addMonthNoOverflow()->utc(),
                'status' => $publish ? ExperienceCommands::timeStatus($starts, $now) : 'draft',
                'details' => $details,
            ])->save();
            $reason = self::text($input['reason'] ?? null);
            $snapshot = ExperienceCommands::snapshot($item);
            $this->versions->record($item, $item->status, $snapshot, $reason === null ? null : mb_substr($reason, 0, 300), $owner);
            // Ids and field names only: the audit never holds a person's name (TeamEditor does the same).
            $this->audit->record($created ? 'recognition.created' : 'recognition.saved', $item, ['after' => [
                'status' => $item->status, 'month' => $details['month'], 'team_member_id' => $memberId, 'media_id' => $photo, 'placements' => $placements,
            ]], actor: $owner);

            return $item;
        });

        return ['item' => $saved, 'errors' => []];
    }

    /**
     * @param  array<string, string|null>  $values
     * @param  list<string>  $placements
     * @param  array<string, string>  $errors
     */
    private function checkPublish(?Experience $item, array $values, ?int $memberId, ?CarbonImmutable $month, ?int $photo, array $placements, CarbonImmutable $now, array &$errors): void
    {
        foreach (['title_ar', 'title_en'] as $field) {
            if ($values[$field] === null) {
                $errors[$field] ??= (string) __('dashboard.recognition.errors.required');
            }
        }
        if (($values['body_ar'] === null) !== ($values['body_en'] === null)) {
            $errors[$values['body_ar'] === null ? 'body_ar' : 'body_en'] ??= (string) __('dashboard.announcements.errors.both_languages');
        }
        $hit = ContentGuard::firstHit($values);
        if ($hit !== null) {
            $errors[$hit[0]] ??= (string) __('dashboard.blocked_phrase', ['phrase' => $hit[1]]);
        }
        if ($placements === []) {
            $errors['placements'] = (string) __('dashboard.recognition.errors.placement');
        }
        if ($month === null) {
            $errors['month'] ??= (string) __('dashboard.recognition.errors.required');
        } else {
            // A month that has ended is history: it may be corrected, not newly published into.
            $kept = $item !== null && $item->status !== 'draft' && ($item->details['month'] ?? null) === $month->format('Y-m');
            if (! $month->addMonthNoOverflow()->greaterThan($now) && ! $kept) {
                $errors['month'] = (string) __('dashboard.recognition.errors.past');
            }
            $taken = Experience::query()->where('type', Recognitions::TYPE)->whereNull('archived_at')->whereIn('status', ['scheduled', 'active'])
                ->when($item?->exists, fn ($q) => $q->whereKeyNot($item?->id))->get()
                ->contains(fn (Experience $other): bool => ($other->details['month'] ?? null) === $month->format('Y-m'));
            if ($taken) {
                $errors['month'] ??= (string) __('dashboard.recognition.errors.taken');
            }
        }
        if ($memberId === null) {
            $errors['team_member_id'] = (string) __('dashboard.recognition.errors.required');

            return;
        }
        // The same checks as the site: the person on SHELTER Family with consent, and a photo the website may use.
        $probe = new Experience(['type' => Recognitions::TYPE, 'details' => ['team_member_id' => $memberId], 'media_id' => $photo, 'placements' => $placements] + $values);
        $problems = $this->recognitions->problems($probe);
        if (in_array('member', $problems, true)) {
            $errors['team_member_id'] = (string) __('dashboard.recognition.errors.member');
        } elseif (in_array('photo', $problems, true)) {
            $errors['media_id'] ??= (string) __('dashboard.recognition.errors.no_photo');
        }
    }

    /** @param  array<string, string>  $errors */
    private function month(mixed $month, mixed $year, array &$errors): ?CarbonImmutable
    {
        $month = is_string($month) || is_int($month) ? trim((string) $month) : '';
        $year = is_string($year) || is_int($year) ? trim((string) $year) : '';
        if ($month === '' && $year === '') {
            return null;
        }
        if (preg_match('/^\d{1,2}$/', $month) !== 1 || preg_match('/^\d{4}$/', $year) !== 1 || (int) $year < 2000 || (int) $year > 2100) {
            $errors['month'] = (string) __('dashboard.recognition.errors.month');

            return null;
        }
        $start = Recognitions::month(sprintf('%04d-%02d', (int) $year, (int) $month));
        if ($start === null) {
            $errors['month'] = (string) __('dashboard.recognition.errors.month');
        }

        return $start;
    }

    private static function text(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim(preg_replace('/[ \t]+/u', ' ', str_replace(["\r\n", "\r"], "\n", $value)) ?? '');

        return $value === '' ? null : $value;
    }
}

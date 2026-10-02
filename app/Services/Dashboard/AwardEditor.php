<?php

namespace App\Services\Dashboard;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Models\Award;
use App\Models\Media;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\MasterData\FactRegistry;
use App\Services\Media\MediaRights;
use Illuminate\Support\Facades\DB;

/**
 * The Owner's awards (no code — M50, PO-032, PUBLISH-GUARD). An award is a public claim, so publishing it takes the
 * Owner's explicit confirmation that it is real and correct: that confirmation approves the fact `award.{id}` in the
 * Fact Registry with exactly these values (title, issuer, year, link). Changing any of them later takes the award
 * offline until the Owner confirms again (the site checks the approved value — Awards). A draft can stay incomplete.
 * Removing = archiving. Every save is audited.
 */
final class AwardEditor
{
    /** What `decision_ref` says for a confirmation given in the Owner Dashboard (the audit holds who and when). */
    public const DECISION_REF = 'OWNER-DASHBOARD';

    private const LIMITS = ['title' => 200, 'issuer' => 200, 'description' => 600, 'evidence_url' => 500];

    /** A sanity range for the year (not a business fact): 2000 … this year. */
    public const FIRST_YEAR = 2000;

    public function __construct(private readonly FactRegistry $facts, private readonly AuditLogger $audit) {}

    /** True when the award's current values are the ones the Owner confirmed (nothing changed since). */
    public function isConfirmed(Award $award): bool
    {
        $fact = $award->exists ? $this->facts->current($award->factKey()) : null;

        return $fact !== null && $fact->status->isPublishable() && $fact->value_hash === FactRegistry::hash($award->factValue());
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{award: Award|null, errors: array<string, string>}
     */
    public function save(?Award $award, array $input, User $owner): array
    {
        $errors = [];
        $values = [];
        foreach (['title', 'issuer', 'description'] as $field) {
            foreach (['ar', 'en'] as $locale) {
                $value = $input[$field.'_'.$locale] ?? null;
                $value = is_string($value) && trim($value) !== '' ? trim(preg_replace('/[ \t]+/u', ' ', str_replace(["\r\n", "\r"], "\n", $value)) ?? '') : null;
                if ($value !== null && mb_strlen($value) > self::LIMITS[$field]) {
                    $errors[$field.'_'.$locale] = (string) __('dashboard.pages.errors.too_long', ['max' => self::LIMITS[$field]]);
                }
                $values[$field.'_'.$locale] = $value;
            }
        }
        $year = $input['year'] ?? null;
        $values['year'] = is_numeric($year) && (int) $year >= self::FIRST_YEAR && (int) $year <= (int) now()->year ? (int) $year : null;
        if ($values['year'] === null) {
            $errors['year'] = (string) __('dashboard.awards.errors.year', ['from' => self::FIRST_YEAR, 'to' => now()->year]);
        }
        $url = is_string($input['evidence_url'] ?? null) ? trim($input['evidence_url']) : '';
        $values['evidence_url'] = $url === '' ? null : $url;
        if ($url !== '' && (! str_starts_with($url, 'https://') || filter_var($url, FILTER_VALIDATE_URL) === false || mb_strlen($url) > self::LIMITS['evidence_url'])) {
            $errors['evidence_url'] = (string) __('dashboard.awards.errors.url');
        }
        $mediaId = is_numeric($input['media_id'] ?? null) ? (int) $input['media_id'] : null;
        if ($mediaId !== null && ! MediaRights::canUse(Media::query()->find($mediaId))) {
            $errors['media_id'] = (string) __('dashboard.awards.errors.image');
        }
        $values['media_id'] = $mediaId;
        $values['sort'] = is_numeric($input['sort'] ?? null) ? max(0, min(999, (int) $input['sort'])) : 0;
        $publish = ($input['status'] ?? '') === 'published';
        $values['status'] = $publish ? 'published' : 'draft';

        if ($publish) {
            foreach (['title_ar', 'title_en', 'issuer_ar', 'issuer_en'] as $field) {
                if ($values[$field] === null) {
                    $errors[$field] ??= (string) __('dashboard.awards.errors.required');
                }
            }
            if (($values['description_ar'] === null) !== ($values['description_en'] === null)) {
                $errors[$values['description_ar'] === null ? 'description_ar' : 'description_en'] = (string) __('dashboard.awards.errors.both_languages');
            }
            $draft = ($award ?? new Award)->forceFill(array_intersect_key($values, array_flip(['title_ar', 'title_en', 'issuer_ar', 'issuer_en', 'year', 'evidence_url'])));
            if (empty($input['confirm']) && ! $this->isConfirmed($draft)) {
                $errors['confirm'] = (string) __('dashboard.awards.errors.confirm');
            }
        }
        if ($errors !== []) {
            $award?->refresh();

            return ['award' => $award, 'errors' => $errors];
        }

        $saved = DB::transaction(function () use ($award, $values, $publish, $input, $owner): Award {
            $award ??= new Award;
            $created = ! $award->exists;
            $before = $award->only(['status', 'title_ar', 'year']);
            $award->forceFill($values)->save();
            $confirmed = false;
            if ($publish && ! empty($input['confirm']) && ! $this->isConfirmed($award)) {
                $this->confirm($award, $owner);
                $confirmed = true;
            }
            $this->audit->record($created ? 'awards.created' : 'awards.saved', $award,
                ['before' => $created ? [] : $before, 'after' => $award->only(['status', 'title_ar', 'year'])], ['confirmed' => $confirmed], actor: $owner);

            return $award;
        });

        return ['award' => $saved, 'errors' => []];
    }

    public function archive(Award $award, User $owner): void
    {
        $award->forceFill(['archived_at' => now()])->save();
        $this->audit->record('awards.archived', $award, ['after' => ['archived' => true]], actor: $owner);
    }

    public function restore(Award $award, User $owner): void
    {
        $award->forceFill(['archived_at' => null])->save();
        $this->audit->record('awards.restored', $award, ['after' => ['archived' => false]], actor: $owner);
    }

    /** The Owner's confirmation = the fact approved with exactly these values (a newer value supersedes the older). */
    private function confirm(Award $award, User $owner): void
    {
        $fact = $this->facts->current($award->factKey());
        if ($fact === null) {
            $fact = $this->facts->register($award->factKey(), 'awards', $award->factValue(), FactStatus::PendingOwnerApproval, FactSource::OwnerDashboard,
                labelAr: 'جائزة: '.$award->title_ar, labelEn: 'Award: '.$award->title_en);
            $this->facts->approve($fact, $owner, self::DECISION_REF);
        } elseif ($fact->status->isPublishable()) {
            $this->facts->supersede($fact, $award->factValue(), $owner, self::DECISION_REF);
        } else {
            $this->facts->approve($fact, $owner, self::DECISION_REF, replaceValue: true, value: $award->factValue());
        }
    }
}

<?php

namespace App\Services\Dashboard;

use App\Models\Branch;
use App\Models\Media;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\MasterData\MasterData;
use App\Services\Media\MediaRights;
use Carbon\CarbonImmutable;

/**
 * The Owner's SHELTER Family profiles (no code — M50, DX-002…006, OPS-034). A profile shows only what the employee
 * agreed to: it is published only with their recorded consent (date + reference to the signed form) and leaves the
 * site the moment a withdrawal is recorded. Name and job title are needed in both languages; the bio and the join
 * date appear only when switched on (and then must be complete). The photo must be an image the site may use —
 * approved, with the person's own consent (MediaRights). No HR data lives here. Removing = archiving; the audit keeps
 * field names only, never a name.
 */
final class TeamEditor
{
    private const LIMITS = ['display_name' => 120, 'job_title' => 120, 'bio' => 600, 'department' => 60, 'consent_ref' => 40];

    private const AUDITED = ['display_name_ar', 'display_name_en', 'job_title_ar', 'job_title_en', 'department', 'branch_id', 'join_date', 'show_join_date',
        'bio_ar', 'bio_en', 'show_bio', 'photo_media_id', 'is_published', 'sort_order', 'publish_consent_at', 'publish_consent_version', 'consent_withdrawn_at'];

    public function __construct(private readonly AuditLogger $audit, private readonly MasterData $data) {}

    /**
     * Branches by their approved name in the dashboard language (id => name); a branch without an approved name is not offered.
     *
     * @return array<int, string>
     */
    public function branches(string $locale): array
    {
        $options = [];
        foreach (Branch::query()->orderBy('id')->get() as $branch) {
            $name = $this->data->branchField($branch, $locale === 'ar' ? 'name_ar' : 'name_en');
            if (is_string($name)) {
                $options[$branch->id] = $name;
            }
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{member: TeamMember|null, errors: array<string, string>}
     */
    public function save(?TeamMember $member, array $input, User $owner): array
    {
        $errors = [];
        $values = [];
        foreach (['display_name', 'job_title', 'bio'] as $field) {
            foreach (['ar', 'en'] as $locale) {
                $values[$field.'_'.$locale] = $this->text($input[$field.'_'.$locale] ?? null, $field, $field.'_'.$locale, $errors);
            }
        }
        $values['department'] = $this->text($input['department'] ?? null, 'department', 'department', $errors);
        $branch = is_numeric($input['branch_id'] ?? null) ? (int) $input['branch_id'] : null;
        $values['branch_id'] = $branch !== null && array_key_exists($branch, $this->branches('ar')) ? $branch : null;
        $values['join_date'] = $this->date($input['join_date'] ?? null, 'join_date', $errors)?->toDateString();
        $values['show_join_date'] = ! empty($input['show_join_date']);
        $values['show_bio'] = ! empty($input['show_bio']);
        $photo = is_numeric($input['photo_media_id'] ?? null) ? (int) $input['photo_media_id'] : null;
        if ($photo !== null && ! MediaRights::canUse(Media::query()->find($photo))) {
            $errors['photo_media_id'] = (string) __('dashboard.team.errors.photo');
        }
        $values['photo_media_id'] = $photo;
        $values['sort_order'] = is_numeric($input['sort_order'] ?? null) ? max(0, min(999, (int) $input['sort_order'])) : 0;

        // Consent (G13-TF-01): the date and the reference of the employee's signed agreement; a withdrawal date ends it.
        $consentAt = $this->date($input['publish_consent_at'] ?? null, 'publish_consent_at', $errors);
        $values['publish_consent_at'] = $consentAt?->utc();
        $values['publish_consent_version'] = $this->text($input['publish_consent_version'] ?? null, 'consent_ref', 'publish_consent_version', $errors);
        $values['consent_withdrawn_at'] = $this->date($input['consent_withdrawn_at'] ?? null, 'consent_withdrawn_at', $errors)?->utc();
        // A recorded withdrawal hides the profile by itself — no second step at the moment it matters most (OPS-034).
        $publish = ($input['is_published'] ?? '') === '1' && $values['consent_withdrawn_at'] === null;
        $values['is_published'] = $publish;

        if ($publish) {
            foreach (['display_name_ar', 'display_name_en', 'job_title_ar', 'job_title_en'] as $field) {
                if ($values[$field] === null) {
                    $errors[$field] ??= (string) __('dashboard.team.errors.required');
                }
            }
            if ($values['publish_consent_at'] === null || $values['publish_consent_version'] === null) {
                $errors[$values['publish_consent_at'] === null ? 'publish_consent_at' : 'publish_consent_version'] ??= (string) __('dashboard.team.errors.consent');
            }
            if ($values['show_bio'] && ($values['bio_ar'] === null || $values['bio_en'] === null)) {
                $errors[$values['bio_ar'] === null ? 'bio_ar' : 'bio_en'] ??= (string) __('dashboard.team.errors.bio');
            }
            if ($values['show_join_date'] && $values['join_date'] === null) {
                $errors['join_date'] ??= (string) __('dashboard.team.errors.join_date');
            }
        }
        if ($errors !== []) {
            return ['member' => $member, 'errors' => $errors];
        }

        $member ??= new TeamMember;
        $created = ! $member->exists;
        $before = $member->only(self::AUDITED);
        $member->forceFill($values)->save();
        $changed = $created ? self::AUDITED : array_values(array_filter(self::AUDITED, fn (string $field): bool => $before[$field] != $member->{$field}));
        $this->audit->record($created ? 'team.created' : 'team.saved', $member, ['after' => ['published' => $member->is_published]], ['changed' => $changed], actor: $owner);

        return ['member' => $member, 'errors' => []];
    }

    public function archive(TeamMember $member, User $owner): void
    {
        $member->forceFill(['archived_at' => now()])->save();
        $this->audit->record('team.archived', $member, ['after' => ['archived' => true]], actor: $owner);
    }

    public function restore(TeamMember $member, User $owner): void
    {
        $member->forceFill(['archived_at' => null])->save();
        $this->audit->record('team.restored', $member, ['after' => ['archived' => false]], actor: $owner);
    }

    /** @param  array<string, string>  $errors */
    private function text(mixed $value, string $limitKey, string $field, array &$errors): ?string
    {
        $value = is_string($value) && trim($value) !== '' ? trim(preg_replace('/[ \t]+/u', ' ', str_replace(["\r\n", "\r"], "\n", $value)) ?? '') : null;
        if ($value !== null && mb_strlen($value) > self::LIMITS[$limitKey]) {
            $errors[$field] = (string) __('dashboard.pages.errors.too_long', ['max' => self::LIMITS[$limitKey]]);
        }

        return $value;
    }

    /**
     * A YYYY-MM-DD date in Amman time, not in the future.
     *
     * @param  array<string, string>  $errors
     */
    private function date(mixed $value, string $field, array &$errors): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim($value), $m) !== 1 || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            $errors[$field] = (string) __('dashboard.media.errors.date');

            return null;
        }
        $date = CarbonImmutable::create((int) $m[1], (int) $m[2], (int) $m[3], 0, 0, 0, 'Asia/Amman');
        if ($date === null || $date->isAfter(CarbonImmutable::now('Asia/Amman')->endOfDay())) {
            $errors[$field] = (string) __('dashboard.team.errors.future');

            return null;
        }

        return $date;
    }
}

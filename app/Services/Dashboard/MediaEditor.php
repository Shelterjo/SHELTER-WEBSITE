<?php

namespace App\Services\Dashboard;

use App\Enums\PublishStatus;
use App\Models\Award;
use App\Models\Experience;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Media\MediaLibrary;
use App\Services\Media\MediaRights;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * The Owner's image library (no code — M50, MEDIA-RIGHTS): upload with the rights the Owner knows, then one screen per
 * image — its alternative text, rights, the people in it and their consent, and the Owner's decision (approve or
 * reject, where it may appear, until when). Approving here IS the Owner's approval (approved_by = the Owner). An image
 * appears on the site only while MediaRights allows it; its public copies are made or taken down to match. Removing =
 * archiving. Every change is audited by field name only (no names of people in the audit).
 */
final class MediaEditor
{
    public const MAX_FILES = 20;

    public const MAX_KB = 20480;

    /** Focus point, % from the left / top edge. */
    public const FOCAL = [0, 25, 50, 75, 100];

    /** What `approval_ref` says for an approval given in the Owner Dashboard (the audit entry holds who and when). */
    public const APPROVAL_REF = 'OWNER-DASHBOARD';

    private const LIMITS = ['photographer' => 150, 'rights_holder' => 150, 'license_note' => 300, 'restrictions' => 500,
        'alt_ar' => 300, 'alt_en' => 300, 'person' => 120, 'document_ref' => 120];

    /** Fields whose change is recorded in the audit (names only). */
    private const AUDITED = ['alt_ar', 'alt_en', 'focal_x', 'focal_y', 'source', 'source_explicitly_approved', 'photographer', 'rights_holder',
        'license', 'license_note', 'restrictions', 'people_consent', 'people_consents', 'approval_status', 'ok_website', 'ok_ads', 'rights_expires_at'];

    public function __construct(private readonly MediaLibrary $library, private readonly AuditLogger $audit) {}

    /**
     * Brings files in with one set of rights; every new image waits for the Owner's approval (MEDIA-003). A file that is
     * already in the library (same content) is not stored twice and keeps its own rights.
     *
     * @param  array<mixed>  $files
     * @param  array<string, mixed>  $input
     * @return array{new: list<Media>, existing: list<Media>, errors: array<string, string>}
     */
    public function upload(array $files, array $input, User $owner): array
    {
        $errors = [];
        $files = array_values(array_filter($files, fn (mixed $file): bool => $file instanceof UploadedFile));
        if ($files === []) {
            $errors['files'] = (string) __('dashboard.media.errors.no_files');
        } elseif (count($files) > self::MAX_FILES) {
            $errors['files'] = (string) __('dashboard.media.errors.too_many', ['max' => self::MAX_FILES]);
        }
        foreach ($files as $file) {
            if (! $file->isValid() || $file->getSize() > self::MAX_KB * 1024) {
                $errors['files'] = (string) __('dashboard.media.errors.too_big', ['name' => $file->getClientOriginalName(), 'max' => self::MAX_KB / 1024]);
            }
        }
        $source = $input['source'] ?? null;
        if (! in_array($source, Media::SOURCES, true)) {
            $errors['source'] = (string) __('dashboard.media.errors.source');
        }
        $people = $input['people_consent'] ?? null;
        if (! in_array($people, ['none', 'not_recorded'], true)) {
            $errors['people_consent'] = (string) __('dashboard.media.errors.people');
        }
        $rights = ['source' => $source, 'people_consent' => $people, 'license' => in_array($input['license'] ?? null, Media::LICENSES, true) ? $input['license'] : 'full'];
        foreach (['photographer', 'rights_holder'] as $field) {
            $rights[$field] = $this->text($input[$field] ?? null, $field, $errors);
        }
        if ($errors !== []) {
            return ['new' => [], 'existing' => [], 'errors' => $errors];
        }

        $new = $existing = [];
        foreach ($files as $file) {
            try {
                $media = $this->library->import((string) $file->getRealPath(), $rights);
            } catch (InvalidArgumentException) {
                $errors['files'] = (string) __('dashboard.media.errors.not_image', ['name' => $file->getClientOriginalName()]);

                continue;
            }
            if ($media->wasRecentlyCreated) {
                $new[] = $media;
                $this->audit->record('media.uploaded', $media, ['after' => ['code' => $media->code, 'source' => $media->source]], actor: $owner);
            } else {
                $existing[] = $media;
            }
        }

        return ['new' => $new, 'existing' => $existing, 'errors' => $errors];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors (field => message); empty = saved
     */
    public function save(Media $media, array $input, User $owner): array
    {
        $errors = [];
        $values = [];
        foreach (['alt_ar', 'alt_en', 'photographer', 'rights_holder', 'license_note', 'restrictions'] as $field) {
            $values[$field] = $this->text($input[$field] ?? null, $field, $errors);
        }
        foreach (['focal_x', 'focal_y'] as $field) {
            $value = (int) ($input[$field] ?? 50);
            $values[$field] = in_array($value, self::FOCAL, true) ? $value : 50;
        }
        $values['source'] = in_array($input['source'] ?? null, Media::SOURCES, true) ? $input['source'] : null;
        if ($values['source'] === null) {
            $errors['source'] = (string) __('dashboard.media.errors.source');
        }
        $values['license'] = in_array($input['license'] ?? null, Media::LICENSES, true) ? $input['license'] : 'full';
        $values['people_consent'] = in_array($input['people_consent'] ?? null, Media::PEOPLE, true) ? $input['people_consent'] : 'not_recorded';
        $values['people_consents'] = $this->people(is_array($input['people'] ?? null) ? $input['people'] : [], $errors);
        $values['ok_website'] = ! empty($input['ok_website']);
        $values['ok_ads'] = ! empty($input['ok_ads']);
        // The date the Owner gives is the last day the image may be used (Amman time).
        $values['rights_expires_at'] = $this->date($input['rights_expires_at'] ?? null, 'rights_expires_at', $errors)?->endOfDay()->utc();
        $restricted = in_array($values['source'], Media::RESTRICTED_SOURCES, true);
        $values['source_explicitly_approved'] = $restricted && ! empty($input['source_explicitly_approved']);
        $decision = in_array($input['approval'] ?? null, ['pending', 'approved', 'rejected'], true) ? $input['approval'] : 'pending';

        // The Owner's approval has to be complete: what a visitor needs (the description in both languages) and what
        // the rights need (the explicit OK for a restricted source, the end date of a time-limited licence, the people).
        if ($decision === 'approved') {
            if ($values['ok_website']) {
                foreach (['alt_ar', 'alt_en'] as $field) {
                    if ($values[$field] === null) {
                        $errors[$field] ??= (string) __('dashboard.media.errors.alt_required');
                    }
                }
            }
            if ($restricted && ! $values['source_explicitly_approved']) {
                $errors['source_explicitly_approved'] = (string) __('dashboard.media.errors.restricted');
            }
            if ($values['license'] === 'time_limited' && $values['rights_expires_at'] === null) {
                $errors['rights_expires_at'] ??= (string) __('dashboard.media.errors.expiry_required');
            }
            if ($values['people_consent'] === 'recorded' && $values['people_consents'] === null) {
                $errors['people'] = (string) __('dashboard.media.errors.people_missing');
            }
        }
        if ($errors !== []) {
            return $errors;
        }

        $values['approval_status'] = match ($decision) {
            'approved' => Media::APPROVED,
            'rejected' => Media::REJECTED,
            default => Media::PENDING,
        };
        if ($decision !== 'approved') {
            $values += ['approved_at' => null, 'approved_by' => null, 'approval_ref' => null];
        } elseif ($media->approval_status !== Media::APPROVED) {
            $values += ['approved_at' => now(), 'approved_by' => $owner->id, 'approval_ref' => self::APPROVAL_REF];
        }

        DB::transaction(function () use ($media, $values, $input, $owner): void {
            $before = $media->only(self::AUDITED);
            $media->forceFill($values)->save();
            $changed = array_values(array_filter(self::AUDITED, fn (string $field): bool => $before[$field] != $media->{$field}));
            $pressKit = $this->setPressKit($media, ! empty($input['press_kit']));
            if ($changed !== [] || $pressKit !== null) {
                $this->audit->record('media.saved', $media, ['after' => ['code' => $media->code, 'status' => $media->approval_status]],
                    ['changed' => $changed, 'press_kit' => $pressKit], actor: $owner);
            }
        });
        $this->syncCopies($media);

        return [];
    }

    public function archive(Media $media, User $owner): void
    {
        $media->forceFill(['archived_at' => now()])->save();
        $this->syncCopies($media);
        $this->audit->record('media.archived', $media, ['after' => ['code' => $media->code]], actor: $owner);
    }

    public function restore(Media $media, User $owner): void
    {
        $media->forceFill(['archived_at' => null])->save();
        $this->syncCopies($media);
        $this->audit->record('media.restored', $media, ['after' => ['code' => $media->code]], actor: $owner);
    }

    public function inPressKit(Media $media): bool
    {
        $page = Page::query()->where('key', 'media')->value('id');

        return $page !== null && MediaUsage::query()->where('media_id', $media->id)->where('usable_type', Page::class)
            ->where('usable_id', $page)->where('slot', 'press_kit')->exists();
    }

    /**
     * Where the image is used, in words for the Owner: the press kit, an award, a SHELTER Family profile, a menu item.
     *
     * @return list<string>
     */
    public function usedIn(Media $media, string $locale): array
    {
        $ar = $locale === 'ar';
        $used = $this->inPressKit($media) ? [(string) __('dashboard.media.used.press_kit')] : [];
        foreach (Award::query()->where('media_id', $media->id)->whereNull('archived_at')->get() as $award) {
            $used[] = (string) __('dashboard.media.used.award', ['name' => ($ar ? $award->title_ar : $award->title_en) ?? $award->title_ar ?? '—']);
        }
        foreach (TeamMember::query()->where('photo_media_id', $media->id)->whereNull('archived_at')->get() as $member) {
            $used[] = (string) __('dashboard.media.used.team', ['name' => ($ar ? $member->display_name_ar : $member->display_name_en) ?? $member->display_name_ar ?? '—']);
        }

        foreach (Product::query()->where('media_id', $media->id)->where('status', Product::STATUS_ACTIVE)->get() as $product) {
            $used[] = (string) __('dashboard.media.used.product', ['name' => (string) $product->display_name_en]);
        }
        foreach (Experience::query()->where('media_id', $media->id)->whereNull('archived_at')->get() as $event) {
            $used[] = (string) __('dashboard.media.used.event', ['name' => ($ar ? $event->title_ar : $event->title_en) ?? $event->title_ar ?? '—']);
        }
        foreach (PageSection::query()->where('media_id', $media->id)->whereNull('archived_at')->with('page')->get() as $section) {
            $used[] = (string) __('dashboard.media.used.page', ['name' => (string) __('dashboard.pages.keys.'.$section->page?->key)]);
        }

        return $used;
    }

    /** Public web copies exist exactly while the image may be used on the website. */
    private function syncCopies(Media $media): void
    {
        if (MediaRights::canUse($media, 'website')) {
            if (empty($media->variants)) {
                $this->library->generateVariants($media);
            }
        } elseif (! empty($media->variants)) {
            $this->library->removeVariants($media);
        }
    }

    /** @return 'added'|'removed'|null */
    private function setPressKit(Media $media, bool $wanted): ?string
    {
        if ($wanted === $this->inPressKit($media)) {
            return null;
        }
        $page = Page::query()->where('key', 'media')->first();
        if ($wanted) {
            // The Media Center page may not be written yet: a draft row holds the press kit until it is (never public).
            $page ??= Page::query()->create(['key' => 'media', 'type' => 'brand', 'status' => PublishStatus::Draft, 'origin' => 'owner']);
            MediaUsage::query()->create(['media_id' => $media->id, 'usable_type' => Page::class, 'usable_id' => $page->id, 'slot' => 'press_kit', 'channel' => 'website']);

            return 'added';
        }
        MediaUsage::query()->where('media_id', $media->id)->where('usable_type', Page::class)->where('usable_id', $page?->id)->where('slot', 'press_kit')->delete();

        return 'removed';
    }

    /**
     * The people in the image and their consent (MEDIA-RIGHTS §4): blank rows are ignored; a filled row needs the
     * person, the date of the consent and at least one place it covers.
     *
     * @param  array<mixed>  $rows
     * @param  array<string, string>  $errors
     * @return list<array{person: string, consented_at: string, scopes: list<string>, document_ref: string|null, withdrawn_at: string|null}>|null
     */
    private function people(array $rows, array &$errors): ?array
    {
        $people = [];
        foreach ($rows as $key => $row) {
            if (! is_array($row) || ! ctype_digit((string) $key)) {
                continue;
            }
            $person = $this->text($row['person'] ?? null, 'person', $errors, "people.{$key}.person");
            $scopes = array_values(array_intersect(Media::SCOPES, is_array($row['scopes'] ?? null) ? $row['scopes'] : []));
            $consented = $this->date($row['consented_at'] ?? null, "people.{$key}.consented_at", $errors);
            $document = $this->text($row['document_ref'] ?? null, 'document_ref', $errors, "people.{$key}.document_ref");
            $withdrawn = $this->date($row['withdrawn_at'] ?? null, "people.{$key}.withdrawn_at", $errors);
            if ($person === null && $consented === null && $document === null && $withdrawn === null) {
                continue; // an untouched blank row (ticked places alone do not make a person)
            }
            if ($person === null) {
                $errors["people.{$key}.person"] = (string) __('dashboard.media.errors.person');
            }
            if ($consented === null) {
                $errors["people.{$key}.consented_at"] ??= (string) __('dashboard.media.errors.consent_date');
            }
            if ($scopes === []) {
                $errors["people.{$key}.scopes"] = (string) __('dashboard.media.errors.scopes');
            }
            $people[] = ['person' => (string) $person, 'consented_at' => (string) $consented?->toDateString(), 'scopes' => $scopes,
                'document_ref' => $document, 'withdrawn_at' => $withdrawn?->toDateString()];
        }

        return $people === [] ? null : $people;
    }

    /** @param  array<string, string>  $errors */
    private function text(mixed $value, string $limitKey, array &$errors, ?string $field = null): ?string
    {
        $value = is_string($value) && trim($value) !== '' ? trim(preg_replace('/\s+/u', ' ', $value) ?? '') : null;
        if ($value !== null && mb_strlen($value) > self::LIMITS[$limitKey]) {
            $errors[$field ?? $limitKey] = (string) __('dashboard.pages.errors.too_long', ['max' => self::LIMITS[$limitKey]]);
        }

        return $value;
    }

    /** @param  array<string, string>  $errors */
    private function date(mixed $value, string $field, array &$errors): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim($value), $m) !== 1 || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            $errors[$field] = (string) __('dashboard.media.errors.date');

            return null;
        }

        return CarbonImmutable::create((int) $m[1], (int) $m[2], (int) $m[3], 0, 0, 0, 'Asia/Amman');
    }
}

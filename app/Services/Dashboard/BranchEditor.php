<?php

namespace App\Services\Dashboard;

use App\Models\Branch;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Core\Versions;
use Illuminate\Support\Facades\DB;

/**
 * A branch's details (M50, MDH-005, PO-010 — the Owner's own facts, no code): its names in both languages, the address
 * and the location description (a nearby landmark, M57) in each language, the Google Maps link and the coordinates, whether it shows on the site, and its services and
 * payment methods (yes · no · not said). Like the hours (HoursEditor), the details are published only after a preview of
 * exactly what visitors will see (BRANCH-010): the preview saves nothing, and publishing refuses details other than the
 * ones previewed (fingerprint). Publishing a value approves it (FACT-REGISTRY: dashboard edit = approval). The
 * fixed address of its page (slug, city — D-053) does not change. Every change is versioned and audited.
 *
 * @phpstan-type Details array{values: array<string, string|null>, public: bool, attributes: array<string, array<string, string>>, reason: string|null, errors: array<string, string>}
 */
final class BranchEditor
{
    public const NAME_MAX = 120;

    public const ADDRESS_MAX = 300;

    /** The text details of a branch, in the form's order (latitude and longitude as the site reads them, 7 decimals). */
    public const FIELDS = ['name_ar', 'name_en', 'address_ar', 'address_en', 'landmark_ar', 'landmark_en', 'maps_url', 'latitude', 'longitude'];

    /** Google Maps link hosts accepted (a share link or a maps page). */
    private const MAP_HOSTS = ['maps.app.goo.gl', 'share.google', 'goo.gl', 'www.google.com', 'google.com', 'maps.google.com', 'www.google.jo', 'google.jo'];

    public function __construct(private readonly OwnerApproval $approval, private readonly Versions $versions, private readonly AuditLogger $audit) {}

    /**
     * Reads the form — nothing is saved: the values as the site will show them, shown or hidden, and the answer for every
     * service and payment method (an answer not sent keeps the current one).
     *
     * @param  array<string, mixed>  $input
     * @return Details
     */
    public function parse(Branch $branch, array $input): array
    {
        $errors = [];
        $text = fn (string $field): ?string => is_string($input[$field] ?? null) && trim($input[$field]) !== ''
            ? trim((string) preg_replace('/\s+/u', ' ', $input[$field])) : null;
        $values = [
            'name_ar' => $text('name_ar'),
            'name_en' => $text('name_en'),
            'address_ar' => $text('address_ar'),
            'address_en' => $text('address_en'),
            'landmark_ar' => $text('landmark_ar'),
            'landmark_en' => $text('landmark_en'),
            'maps_url' => $text('maps_url'),
        ];
        foreach (['name_ar', 'name_en'] as $field) {
            if ($values[$field] === null) {
                $errors[$field] = (string) __('dashboard.branch.errors.name');
            } elseif (mb_strlen($values[$field]) > self::NAME_MAX) {
                $errors[$field] = (string) __('dashboard.pages.errors.too_long', ['max' => self::NAME_MAX]);
            }
        }
        foreach (['address_ar', 'address_en', 'landmark_ar', 'landmark_en'] as $field) {
            if ($values[$field] !== null && mb_strlen($values[$field]) > self::ADDRESS_MAX) {
                $errors[$field] = (string) __('dashboard.pages.errors.too_long', ['max' => self::ADDRESS_MAX]);
            }
        }
        if ($values['maps_url'] !== null && ! self::mapsLink($values['maps_url'])) {
            $errors['maps_url'] = (string) __('dashboard.branch.errors.maps');
        }
        $lat = is_string($input['latitude'] ?? null) ? trim($input['latitude']) : '';
        $lng = is_string($input['longitude'] ?? null) ? trim($input['longitude']) : '';
        if (($lat === '') !== ($lng === '')) {
            $errors[$lat === '' ? 'latitude' : 'longitude'] = (string) __('dashboard.branch.errors.both_coordinates');
        } elseif ($lat !== '' && (! is_numeric($lat) || abs((float) $lat) > 90 || ! is_numeric($lng) || abs((float) $lng) > 180)) {
            $errors['latitude'] = (string) __('dashboard.branch.errors.coordinates');
        }
        $values['latitude'] = $errors === [] && $lat !== '' ? number_format((float) $lat, 7, '.', '') : null;
        $values['longitude'] = $errors === [] && $lng !== '' ? number_format((float) $lng, 7, '.', '') : null;

        $sent = is_array($input['attributes'] ?? null) ? $input['attributes'] : [];
        $attributes = [];
        foreach ($branch->branchAttributes()->orderBy('id')->get() as $attribute) {
            $choice = is_array($sent[$attribute->group] ?? null) ? ($sent[$attribute->group][$attribute->key] ?? null) : null;
            $attributes[$attribute->group][$attribute->key] = in_array($choice, ['yes', 'no', 'unknown'], true)
                ? $choice : ($attribute->value === null ? 'unknown' : ($attribute->value ? 'yes' : 'no'));
        }
        ksort($attributes);

        return [
            'values' => $values,
            'public' => ($input['is_public'] ?? '1') !== '0',
            'attributes' => $attributes,
            'reason' => is_string($input['reason'] ?? null) && trim($input['reason']) !== '' ? mb_substr(trim($input['reason']), 0, 300) : null,
            'errors' => $errors,
        ];
    }

    /**
     * What the preview stands for: these exact details, so "publish" publishes what was seen (as HoursEditor does).
     *
     * @param  Details  $details
     */
    public static function fingerprint(Branch $branch, array $details): string
    {
        return hash('sha256', $branch->code.'|'.json_encode([$details['values'], $details['public'], $details['attributes']]));
    }

    /**
     * The services and payment methods the site will list: the ones answered yes.
     *
     * @param  Details  $details
     * @return array<string, list<string>> group => keys
     */
    public static function said(array $details): array
    {
        $said = ['service' => [], 'payment' => []];
        foreach ($details['attributes'] as $group => $answers) {
            $said[$group] = array_keys(array_filter($answers, fn (string $choice): bool => $choice === 'yes'));
        }

        return $said;
    }

    /**
     * The fields these details change, named as the form names them (the preview lists them for the Owner to check).
     *
     * @param  Details  $details
     * @return list<string>
     */
    public function changes(Branch $branch, array $details): array
    {
        $now = self::snapshot($branch, self::FIELDS);
        $changed = [];
        foreach ($details['values'] as $field => $value) {
            if ($now[$field] !== $value) {
                $changed[] = (string) __('dashboard.branch.'.$field);
            }
        }
        if ($branch->is_public !== $details['public']) {
            $changed[] = (string) __('dashboard.branch.is_public');
        }
        foreach ($branch->branchAttributes()->orderBy('id')->get() as $attribute) {
            $choice = $attribute->value === null ? 'unknown' : ($attribute->value ? 'yes' : 'no');
            if (($details['attributes'][$attribute->group][$attribute->key] ?? $choice) !== $choice) {
                $changed[] = (string) __('site.attributes.'.$attribute->group.'.'.$attribute->key);
            }
        }

        return $changed;
    }

    /**
     * Publishes the previewed details: refused when they are not the ones previewed.
     *
     * @param  Details  $details
     * @return array<string, string> errors; empty = published
     */
    public function publish(Branch $branch, array $details, string $previewed, User $owner): array
    {
        if ($details['errors'] !== []) {
            return $details['errors'];
        }
        if (! hash_equals(self::fingerprint($branch, $details), $previewed)) {
            return ['preview' => (string) __('dashboard.branch.errors.preview')];
        }
        $values = $details['values'];
        $public = $details['public'];
        $attributes = $details['attributes'];
        $reason = $details['reason'];

        DB::transaction(function () use ($branch, $values, $public, $attributes, $reason, $owner): void {
            $fields = [...array_keys($values), 'is_public'];
            $before = self::snapshot($branch, $fields);
            $branch->forceFill($values + ['is_public' => $public])->save();
            foreach (array_keys($values) as $field) {
                $stored = $branch->getAttribute($field); // approved exactly as the site reads it
                if ($stored !== null) {
                    $this->approval->approve($branch->factKey($field), $stored, $owner, 'branch');
                }
            }
            $changedAttributes = [];
            foreach ($branch->branchAttributes()->get() as $attribute) {
                $choice = $attributes[$attribute->group][$attribute->key] ?? null;
                if ($choice === null) {
                    continue;
                }
                $value = $choice === 'unknown' ? null : $choice === 'yes';
                if ($attribute->value !== $value) {
                    $changedAttributes[$attribute->group.'.'.$attribute->key] = $choice;
                    $attribute->forceFill(['value' => $value])->save();
                }
            }
            $after = self::snapshot($branch->refresh(), $fields);
            if ($after === $before && $changedAttributes === []) {
                return;
            }
            $this->versions->record($branch, 'published', ['code' => $branch->code] + $after + ['attributes' => $changedAttributes], $reason, $owner);
            $this->audit->record('branch.details_saved', $branch, ['before' => array_diff_assoc($before, $after), 'after' => array_diff_assoc($after, $before) + $changedAttributes],
                $reason === null ? [] : ['reason' => $reason], actor: $owner);
        });

        return [];
    }

    /** An https link to Google Maps (a share link or a maps page). */
    public static function mapsLink(string $url): bool
    {
        $parts = parse_url($url);
        $host = is_array($parts) ? strtolower($parts['host'] ?? '') : '';
        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || ! in_array($host, self::MAP_HOSTS, true)) {
            return false;
        }

        // A share link (maps.app.goo.gl, or share.google — Google's newer "Share" link, path = the code) or the Maps
        // host itself; elsewhere only a /maps page.
        if ($host === 'share.google') {
            return (bool) preg_match('#^/[A-Za-z0-9_-]+$#', $parts['path'] ?? '');
        }

        return in_array($host, ['maps.app.goo.gl', 'maps.google.com'], true) || str_starts_with($parts['path'] ?? '', '/maps');
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, string|null>
     */
    private static function snapshot(Branch $branch, array $fields): array
    {
        $out = [];
        foreach ($fields as $field) {
            $value = $branch->getAttribute($field);
            $out[$field] = is_bool($value) ? ($value ? '1' : '0') : ($value === null ? null : (string) $value);
        }

        return $out;
    }
}

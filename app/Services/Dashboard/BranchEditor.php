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
 * payment methods (yes · no · not said). Saving a value approves it (FACT-REGISTRY: dashboard edit = approval). The
 * fixed address of its page (slug, city — D-053) does not change. Every change is versioned and audited.
 */
final class BranchEditor
{
    public const NAME_MAX = 120;

    public const ADDRESS_MAX = 300;

    /** Google Maps link hosts accepted (a share link or a maps page). */
    private const MAP_HOSTS = ['maps.app.goo.gl', 'goo.gl', 'www.google.com', 'google.com', 'maps.google.com', 'www.google.jo', 'google.jo'];

    public function __construct(private readonly OwnerApproval $approval, private readonly Versions $versions, private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors; empty = saved
     */
    public function save(Branch $branch, array $input, User $owner): array
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
        $attributes = is_array($input['attributes'] ?? null) ? $input['attributes'] : [];
        if ($errors !== []) {
            return $errors;
        }
        $values['latitude'] = $lat === '' ? null : number_format((float) $lat, 7, '.', '');
        $values['longitude'] = $lng === '' ? null : number_format((float) $lng, 7, '.', '');
        $public = ($input['is_public'] ?? '1') !== '0';
        $reason = is_string($input['reason'] ?? null) && trim($input['reason']) !== '' ? mb_substr(trim($input['reason']), 0, 300) : null;

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
                if (! in_array($choice, ['yes', 'no', 'unknown'], true)) {
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

        // A share link (maps.app.goo.gl) or the Maps host itself; elsewhere only a /maps page.
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

<?php

namespace App\Services\Requests;

use App\Models\Recruitment\InterviewLocation;
use App\Models\Recruitment\JordanCity;
use App\Models\User;
use App\Services\Core\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Careers settings (CAREERS-071/075): when an open application counts as "waiting too long" (a nudge in the list only —
 * nothing is ever deleted by age), the interview locations and the city list of the form. A place or a city is
 * switched off, never deleted, so older applications keep the value they were sent with. Every change is audited.
 */
final class RecruitmentSettings
{
    /** Days without any update before an open application is flagged (a UX default, changeable here). */
    public const STALE_DAYS_DEFAULT = 14;

    private const NAME_MAX = 80;

    public function __construct(private readonly AuditLogger $audit) {}

    public static function staleDays(): int
    {
        $value = DB::table('recruitment_settings')->where('key', 'stale_days')->value('value_json');
        $days = is_string($value) ? (int) json_decode($value, true) : self::STALE_DAYS_DEFAULT;

        return $days >= 1 && $days <= 365 ? $days : self::STALE_DAYS_DEFAULT;
    }

    /** @return array<string, string> errors; empty = saved */
    public function setStaleDays(mixed $days, User $owner): array
    {
        if (! is_numeric($days) || (int) $days < 1 || (int) $days > 365) {
            return ['stale_days' => (string) __('dashboard.requests.settings.errors.days')];
        }
        $before = self::staleDays();
        DB::table('recruitment_settings')->updateOrInsert(['key' => 'stale_days'], ['value_json' => json_encode((int) $days), 'updated_at' => now(), 'created_at' => now()]);
        $this->audit->record('careers.settings_changed', null, ['before' => ['stale_days' => $before], 'after' => ['stale_days' => (int) $days]], actor: $owner);

        return [];
    }

    /**
     * Adds a place, or renames / switches one on or off.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors; empty = saved
     */
    public function saveLocation(?InterviewLocation $location, array $input, User $owner): array
    {
        $ar = self::text($input['name_ar'] ?? null);
        $en = self::text($input['name_en'] ?? null);
        $errors = [];
        foreach (['name_ar' => $ar, 'name_en' => $en] as $key => $value) {
            if ($value === null || mb_strlen($value) > self::NAME_MAX) {
                $errors[$key] = (string) __('dashboard.requests.settings.errors.name');
            }
        }
        if ($errors !== []) {
            return $errors;
        }
        $location ??= new InterviewLocation(['sort_order' => (int) InterviewLocation::query()->max('sort_order') + 1]);
        $before = $location->exists ? $location->only(['name_ar', 'name_en', 'is_active']) : [];
        $location->forceFill(['name_ar' => $ar, 'name_en' => $en, 'is_active' => $location->exists ? ! empty($input['is_active']) : true])->save();
        $this->audit->record('careers.location_saved', $location, ['before' => $before, 'after' => $location->only(['name_ar', 'name_en', 'is_active'])], actor: $owner);

        return [];
    }

    /**
     * Adds a city, or switches one on or off (the form lists only the active ones).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors; empty = saved
     */
    public function saveCity(?JordanCity $city, array $input, User $owner): array
    {
        if ($city === null) {
            $ar = self::text($input['name_ar'] ?? null);
            if ($ar === null || mb_strlen($ar) > self::NAME_MAX) {
                return ['city_name_ar' => (string) __('dashboard.requests.settings.errors.name')];
            }
            if (JordanCity::query()->where('name_ar', $ar)->exists()) {
                return ['city_name_ar' => (string) __('dashboard.requests.settings.errors.city_exists')];
            }
            $city = JordanCity::query()->create(['name_ar' => $ar, 'name_en' => self::text($input['name_en'] ?? null), 'is_active' => true,
                'sort_order' => (int) JordanCity::query()->max('sort_order') + 1, 'source' => 'Owner — dashboard', 'verification_status' => 'APPROVED']);
            $this->audit->record('careers.city_added', $city, ['after' => ['name_ar' => $ar]], actor: $owner);

            return [];
        }
        $active = ! empty($input['is_active']);
        if ($active !== $city->is_active) {
            $city->forceFill(['is_active' => $active])->save();
            $this->audit->record('careers.city_switched', $city, ['after' => ['is_active' => $active]], actor: $owner);
        }

        return [];
    }

    private static function text(mixed $value): ?string
    {
        $value = is_string($value) ? trim((string) preg_replace('/\s+/u', ' ', $value)) : '';

        return $value === '' ? null : $value;
    }
}

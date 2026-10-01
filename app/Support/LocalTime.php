<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Clock times as the decisions write them (D-020, HOURS-010): 12-hour with ص/م in Arabic and AM/PM in English,
 * Western digits in both ("2:00 ص" · "2:00 AM"). Weekday names come from lang/{locale}/site.php.
 */
final class LocalTime
{
    public static function format(CarbonInterface $time, string $locale): string
    {
        $suffix = $time->hour < 12 ? 'am' : 'pm';

        return $time->format('g:i').' '.__('site.time.'.$suffix, [], $locale);
    }

    /** "HH:MM" (database form) → display form, without a date. */
    public static function clock(string $hhmm, string $locale): string
    {
        [$hour, $minute] = array_map('intval', explode(':', $hhmm) + [0, 0]);
        $suffix = $hour < 12 ? 'am' : 'pm';
        $display = ($hour % 12 === 0 ? 12 : $hour % 12).':'.str_pad((string) $minute, 2, '0', STR_PAD_LEFT);

        return $display.' '.__('site.time.'.$suffix, [], $locale);
    }

    public static function weekday(int $dayOfWeek, string $locale): string
    {
        return (string) __('site.weekdays.'.$dayOfWeek, [], $locale);
    }
}

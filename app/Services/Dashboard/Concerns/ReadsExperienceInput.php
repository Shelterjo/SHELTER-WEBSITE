<?php

namespace App\Services\Dashboard\Concerns;

use Carbon\CarbonImmutable;

/** Reading the dashboard's experience forms (events, announcements, campaigns): text, a safe link, a date and time. */
trait ReadsExperienceInput
{
    /** A link the site may follow: https:// anywhere, or a path on this site (G-08). */
    private static function validLink(string $url): bool
    {
        if (mb_strlen($url) > 500) {
            return false;
        }
        if (str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//') && preg_match('#^/[A-Za-z0-9\-._~/%?=&]*$#', $url) === 1;
        }

        return str_starts_with($url, 'https://') && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /** @param  array<string, string>  $errors */
    private static function moment(mixed $date, mixed $time, string $timezone, string $prefix, array &$errors): ?CarbonImmutable
    {
        $date = is_string($date) ? trim($date) : '';
        $time = is_string($time) ? trim($time) : '';
        if ($date === '' && $time === '') {
            return null;
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $d) !== 1 || ! checkdate((int) $d[2], (int) $d[3], (int) $d[1])) {
            $errors[$prefix.'_date'] = (string) __('dashboard.hours.errors.date');

            return null;
        }
        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time, $t) !== 1) {
            $errors[$prefix.'_time'] = (string) __('dashboard.events.errors.time');

            return null;
        }

        return CarbonImmutable::create((int) $d[1], (int) $d[2], (int) $d[3], (int) $t[1], (int) $t[2], 0, $timezone);
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

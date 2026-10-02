<?php

namespace App\Support;

/**
 * The "back" address of a dashboard screen, taken from the previous page only when that page is a dashboard page on
 * this site (FINAL-QA QA-010). url()->previous() trusts the Referer header first, so an outside link to a screen that
 * answers GET would otherwise send the Owner — or the Cancel button — to the outside site.
 */
final class DashboardBack
{
    public static function to(string $fallback): string
    {
        $previous = url()->previous();
        $here = request()->getSchemeAndHttpHost();
        $path = (string) parse_url($previous, PHP_URL_PATH);

        return str_starts_with($previous, $here.'/') && preg_match('#^/dashboard(/|$)#', $path) === 1 ? $previous : $fallback;
    }
}

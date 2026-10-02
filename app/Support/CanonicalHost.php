<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * Every generated address — canonical, hreflang, sitemap, llms.txt, redirects — uses the configured site address
 * (APP_URL) on staging and production, never the Host header of the request (FINAL-QA QA-012). Otherwise the server's
 * default domain, a non-www host or a forged Host header would get self-canonicals. Local and test runs keep the
 * request's own host so preview servers on any port keep working.
 */
final class CanonicalHost
{
    public static function apply(string $environment, string $appUrl): void
    {
        if (! in_array($environment, ['production', 'staging'], true) || filter_var($appUrl, FILTER_VALIDATE_URL) === false) {
            return;
        }
        URL::forceRootUrl(rtrim($appUrl, '/'));
        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }
}

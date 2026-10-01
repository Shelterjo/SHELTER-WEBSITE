<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * Canonical public URLs always end with a slash (/ar/jo/menu/). Use this for every internal link,
 * canonical and hreflang so the site never links to a URL that 301s.
 */
final class PageUrl
{
    /** @param array<string, mixed> $parameters */
    public static function route(string $name, array $parameters = []): string
    {
        $url = URL::route($name, $parameters);
        $path = (string) parse_url($url, PHP_URL_PATH);

        return str_ends_with($path, '/') ? $url : preg_replace('#^([^?]*)(\?.*)?$#', '$1/$2', $url, 1) ?? $url;
    }

    /**
     * hreflang alternates for a localized page. x-default is set only where a decision allows it
     * (D-052: the root gateway; non-root pages wait for PO-005).
     *
     * @param  array<string, mixed>  $parameters
     * @return array<string, string> hreflang => url
     */
    public static function alternates(string $name, array $parameters = []): array
    {
        $out = [];
        /** @var list<string> $locales */
        $locales = config('shelter.locales');
        foreach ($locales as $locale) {
            $out[$locale] = self::route($name, ['locale' => $locale] + $parameters);
        }

        return $out;
    }
}

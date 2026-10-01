<?php

namespace App\Support;

use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Route;

/**
 * Canonical URL of a named page only if that page exists (Route::has) — navigation, footer, CTAs and error pages link
 * to pages as they are added (e.g. the Menu page) without a dead link before. Only the parameters the route declares
 * are passed, so a brand-layer page can offer {locale, market} to any route without leaking query strings.
 */
final class SiteLinks
{
    /** @param array<string, mixed> $parameters */
    public static function to(string $name, array $parameters = []): ?string
    {
        $route = Route::has($name) ? Route::getRoutes()->getByName($name) : null;
        if ($route === null) {
            return null;
        }
        $declared = array_flip($route->parameterNames());

        try {
            return PageUrl::route($name, array_intersect_key($parameters, $declared));
        } catch (UrlGenerationException) {
            return null;
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only production with SHELTER_INDEXING=true is indexable (ENVIRONMENTS.md, SEO-028). The dashboard,
 * auth screens and form states are never indexable, in any environment.
 */
final class Indexing
{
    public static function siteIndexable(): bool
    {
        return app()->isProduction() && config('shelter.indexing') === true;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! self::siteIndexable() || $request->is('dashboard', 'dashboard/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        return $response;
    }
}

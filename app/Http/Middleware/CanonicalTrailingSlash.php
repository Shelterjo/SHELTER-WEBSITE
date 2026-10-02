<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Public page URLs have exactly one form: a trailing slash (/ar/jo/menu/). GET/HEAD requests without it
 * get one 301 to the canonical URL (no chains). Files (robots.txt, sitemap.xml) and the dashboard are untouched.
 * An address no route answers gets its 404 at once, never a 301 to a 404 (FINAL-QA QA-051).
 */
final class CanonicalTrailingSlash
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if ($request->isMethodSafe() && $path !== '/' && ! str_ends_with($path, '/') && ! str_contains(basename($path), '.')
            && ! $request->is('dashboard', 'dashboard/*', 'up') && $this->routed($request)) {
            $query = $request->getQueryString();

            return redirect()->to($request->getSchemeAndHttpHost().$request->getBaseUrl().$path.'/'.($query !== null ? '?'.$query : ''), 301);
        }

        return $next($request);
    }

    private function routed(Request $request): bool
    {
        try {
            Route::getRoutes()->match($request);

            return true;
        } catch (HttpException) {
            return false;
        }
    }
}

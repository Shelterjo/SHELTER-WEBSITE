<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as MatchedRoute;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Every public page URL has exactly one form: single slashes and a trailing slash (/ar/jo/menu/). A GET/HEAD request
 * for another spelling of a page gets ONE 301 to that form, the query string kept (no chains):
 * - the trailing slash missing (/ar/jo/menu);
 * - repeated slashes anywhere in the path (/ar//jo/menu/, /ar/jo/menu//) — PUBLIC-ROUTE-MAP RM-03;
 * - the front script typed into the address (/index.php/ar/jo/menu/, /index.php) — RM-02.
 * Files (robots.txt, sitemap.xml) and the dashboard keep their own form (no slash added); only the extra slashes and
 * the script name go. An address no route answers gets its 404 at once, never a 301 to a 404 (FINAL-QA QA-051).
 *
 * A page that has a route but answers 404 — content not published yet (/ar/about), an unknown branch slug — is asked
 * first (RM-01): the request is run once, and anything but a success (404, 410, the Owner's redirect for that address,
 * the page's own redirect) is returned as it is, so there is no 301 in front of a dead end. Only a page that answers
 * gets the 301. This costs one extra render for these mistyped addresses only (no internal link uses them). Not asked:
 * the dashboard (its GET screens can be heavy, e.g. exports) and the routes in NOT_PROBED. A path with repeated slashes
 * inside it does not match its route as typed, so it is not asked either: its 301 goes to the page, which then answers.
 */
final class CanonicalTrailingSlash
{
    /** Routes whose GET has a side effect (the anonymous search count, PO-019) and that never answer 404. */
    private const NOT_PROBED = ['search'];

    /** Paths that never take a trailing slash. */
    private const NO_SLASH = '#^/(dashboard|up)(/|$)#';

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $path = $request->getPathInfo();
        $script = str_ends_with($request->getBaseUrl(), '/index.php');
        $target = (string) preg_replace('#/{2,}#', '/', $path);
        $isPage = $target !== '/' && ! str_contains(basename($target), '.') && preg_match(self::NO_SLASH, $target) !== 1;
        if ($isPage && ! str_ends_with($target, '/')) {
            $target .= '/';
        }
        if ($target === $path && ! $script) {
            return $next($request);
        }

        $route = $this->match($request);
        if ($route === null && $this->match(Request::create($target)) === null) {
            return $next($request); // nothing answers this address: the router's 404, at once
        }
        if ($route !== null && $isPage && ! in_array($route->getName(), self::NOT_PROBED, true)) {
            $answer = $next($request);
            if (! $answer->isSuccessful()) {
                return $answer;
            }
        }

        $base = $script ? substr($request->getBaseUrl(), 0, -strlen('/index.php')) : $request->getBaseUrl();
        $query = $request->getQueryString();

        return redirect()->to($request->getSchemeAndHttpHost().$base.$target.($query !== null ? '?'.$query : ''), 301);
    }

    private function match(Request $request): ?MatchedRoute
    {
        try {
            return Route::getRoutes()->match($request);
        } catch (HttpException) {
            return null;
        }
    }
}

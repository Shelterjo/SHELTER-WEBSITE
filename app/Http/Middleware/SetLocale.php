<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locale comes from the URL prefix only (/ar/ · /en/), never from IP or browser language (D-067: no geo/IP redirect).
 */
final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        /** @var list<string> $supported */
        $supported = config('shelter.locales');
        if (! is_string($locale) || ! in_array($locale, $supported, true)) {
            abort(404);
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);
        View::share('dir', $locale === 'ar' ? 'rtl' : 'ltr');
        $request->route()?->forgetParameter('locale');
        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}

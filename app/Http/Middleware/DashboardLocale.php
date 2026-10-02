<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** The dashboard speaks the Owner's language (DASH-034: Arabic first) whatever the site's default locale is. */
final class DashboardLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('shelter.dashboard_locale');
        app()->setLocale(is_string($locale) && in_array($locale, ['ar', 'en'], true) ? $locale : 'ar');

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Market;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the market from the URL (/ar/jo/…). Only active markets resolve; there is no country hardcoding.
 */
final class ResolveMarket
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = $request->route('market');
        $market = is_string($code) ? Market::query()->where('code', $code)->where('is_active', true)->first() : null;
        if ($market === null) {
            abort(404);
        }

        app()->instance(Market::class, $market);
        URL::defaults(['market' => $market->code]);
        $request->route()?->forgetParameter('market');

        return $next($request);
    }
}

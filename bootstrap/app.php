<?php

use App\Http\Middleware\BasicAuthGate;
use App\Http\Middleware\CanonicalTrailingSlash;
use App\Http\Middleware\Indexing;
use App\Http\Middleware\ResolveMarket;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Staging gate first, then headers on every response (including errors and redirects).
        $middleware->prepend(BasicAuthGate::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->append(Indexing::class);
        $middleware->web(prepend: [CanonicalTrailingSlash::class]);
        $middleware->alias([
            'locale' => SetLocale::class,
            'market' => ResolveMarket::class,
        ]);
        // Trust only known proxies (Cloudways local stack + Cloudflare ranges set in TRUSTED_PROXIES on the server),
        // so a client cannot spoof X-Forwarded-For to dodge rate limits. Never '*'.
        $proxies = env('TRUSTED_PROXIES', '127.0.0.1,::1');
        $middleware->trustProxies(at: array_values(array_filter(array_map('trim', explode(',', is_string($proxies) ? $proxies : '')))));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

<?php

use App\Http\Middleware\BasicAuthGate;
use App\Http\Middleware\CanonicalTrailingSlash;
use App\Http\Middleware\EnsureOwner;
use App\Http\Middleware\Indexing;
use App\Http\Middleware\RequireRecentConfirmation;
use App\Http\Middleware\ResolveMarket;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Services\Forms\FormGuard;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            'owner' => EnsureOwner::class,
            'confirmed' => RequireRecentConfirmation::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard.home'));
        // Only the configured site domain (www, the bare domain and its subdomains) is answered outside local and tests
        // (FINAL-QA QA-012): a forged Host header gets a 400 instead of a page.
        $middleware->trustHosts(at: function (): array {
            $host = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

            return $host === '' ? [] : ['^(.+\.)?'.preg_quote((string) preg_replace('/^www\./', '', $host)).'$'];
        }, subdomains: false);
        // Trust only known proxies (Cloudways local stack + Cloudflare ranges set in TRUSTED_PROXIES on the server),
        // so a client cannot spoof X-Forwarded-For to dodge rate limits. Never '*'.
        $proxies = env('TRUSTED_PROXIES', '127.0.0.1,::1');
        $middleware->trustProxies(at: array_values(array_filter(array_map('trim', explode(',', is_string($proxies) ? $proxies : '')))));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        // FINAL-QA QA-006: a public form left open past the session lifetime goes back to the same form with what the
        // visitor typed and one clear message — never a dead end. Identity numbers, files and secrets are not kept.
        // (The framework turns the token mismatch into an HTTP 419 before render callbacks run.)
        $exceptions->render(function (HttpException $e, Request $request) {
            if (! $e->getPrevious() instanceof TokenMismatchException
                || $request->expectsJson() || ! $request->isMethod('POST') || ! preg_match('#^(ar|en)/#', $request->path().'/', $locale)) {
                return null;
            }

            return redirect()->to(rtrim($request->url(), '/').'/') // public pages end with a slash: no extra hop
                ->withInput($request->except(['_token', 'national_id', 'document_number', 'files', 'password', 'code', FormGuard::HONEYPOT]))
                ->withErrors(['form' => __('site.errors.form_expired', [], $locale[1])]); // the locale middleware has not run yet
        });
    })->create();

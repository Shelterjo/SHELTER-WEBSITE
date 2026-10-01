<?php

namespace App\Http\Middleware;

use App\Services\Auth\OwnerSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sensitive actions (export, reveal identity numbers, permanent delete, rollback, disconnect an integration,
 * Safe Mode) need password + TOTP re-confirmation within the last few minutes (SECURITY-CENTER.md).
 */
final class RequireRecentConfirmation
{
    public function __construct(private readonly OwnerSession $owner) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->owner->recentlyConfirmed($request->session())) {
            if ($request->isMethodSafe()) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('dashboard.confirm');
        }

        return $next($request);
    }
}

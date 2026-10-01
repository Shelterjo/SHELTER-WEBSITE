<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Auth\OwnerSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Server-side owner gate for every dashboard route (PERM-*, M30). Requires: an authenticated, active owner
 * with a confirmed second factor, inside the absolute session lifetime. Hiding buttons is never the control.
 */
final class EnsureOwner
{
    public function __construct(private readonly OwnerSession $owner) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User || ! $user->isOwner() || ! $user->hasTwoFactorEnabled()) {
            if ($user !== null) {
                $this->owner->logout($request->session(), $user instanceof User ? $user : null, 'not_owner');
            }

            return redirect()->guest(route('login'));
        }
        if (! $this->owner->isWithinAbsoluteLifetime($request->session())) {
            $this->owner->logout($request->session(), $user, 'absolute_timeout');

            return redirect()->route('login')->with('status', 'session_expired');
        }

        return $next($request);
    }
}

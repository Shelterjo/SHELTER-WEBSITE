<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP basic auth in front of the whole app on staging (ENVIRONMENTS.md). Disabled when no user is configured.
 * The /up health endpoint stays open so uptime checks work.
 */
final class BasicAuthGate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = config('shelter.basic_auth.user');
        $password = config('shelter.basic_auth.password');
        if (! is_string($user) || $user === '' || $request->is('up')) {
            return $next($request);
        }

        $givenUser = (string) $request->getUser();
        $givenPassword = (string) $request->getPassword();
        if (! hash_equals($user, $givenUser) || ! is_string($password) || ! hash_equals($password, $givenPassword)) {
            return response('Authentication required.', 401, ['WWW-Authenticate' => 'Basic realm="SHELTER staging"']);
        }

        return $next($request);
    }
}

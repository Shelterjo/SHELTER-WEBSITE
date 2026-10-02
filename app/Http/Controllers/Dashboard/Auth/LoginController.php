<?php

namespace App\Http\Controllers\Dashboard\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\LoginRequest;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Core\AuditLogger;
use App\Support\Input;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class LoginController extends Controller
{
    public function __construct(private readonly OwnerSession $owner, private readonly AuditLogger $audit) {}

    public function show(): View
    {
        return view('dashboard.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $key = $request->throttleKey();
        /** @var int $max */
        $max = config('shelter.auth.max_attempts_per_minute');
        if (RateLimiter::tooManyAttempts($key, $max)) {
            $this->audit->record('auth.locked', meta: ['email_hash' => hash('sha256', mb_strtolower(Input::text($request, 'email')))]);
            throw ValidationException::withMessages(['email' => __('dashboard.auth.throttled', ['seconds' => RateLimiter::availableIn($key)])]);
        }

        $user = $this->owner->checkPassword(Input::text($request, 'email'), Input::text($request, 'password'));
        if ($user === null) {
            RateLimiter::hit($key, 60);
            $this->audit->record('auth.failed', meta: ['email_hash' => hash('sha256', mb_strtolower(Input::text($request, 'email')))]);
            throw ValidationException::withMessages(['email' => __('dashboard.auth.failed')]);
        }

        RateLimiter::clear($key);
        $this->owner->startPending($request->session(), $user);

        return redirect()->route($user->hasTwoFactorEnabled() ? 'two-factor.challenge' : 'two-factor.setup');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->owner->logout($request->session(), $user instanceof User ? $user : null);

        return redirect()->route('login');
    }
}

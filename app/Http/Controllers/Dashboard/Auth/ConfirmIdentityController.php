<?php

namespace App\Http\Controllers\Dashboard\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Auth\TwoFactor;
use App\Services\Core\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/** Password + TOTP re-confirmation before sensitive actions (RequireRecentConfirmation). */
final class ConfirmIdentityController extends Controller
{
    public function __construct(private readonly TwoFactor $twoFactor, private readonly AuditLogger $audit) {}

    public function show(): View
    {
        return view('dashboard.auth.confirm');
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate(['password' => ['required', 'string', 'max:200'], 'code' => ['required', 'string', 'max:12']]);
        $key = "confirm:{$user->id}";
        /** @var int $max */
        $max = config('shelter.auth.max_attempts_per_minute');
        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw ValidationException::withMessages(['password' => __('dashboard.auth.throttled', ['seconds' => RateLimiter::availableIn($key)])]);
        }
        if (! Hash::check($data['password'], $user->password) || ! $this->twoFactor->verify($user, $data['code'])) {
            RateLimiter::hit($key, 60);
            $this->audit->record('auth.confirm_failed', $user, actor: $user);
            throw ValidationException::withMessages(['password' => __('dashboard.auth.confirm_failed')]);
        }

        RateLimiter::clear($key);
        $request->session()->put(OwnerSession::CONFIRMED_AT, now()->getTimestamp());
        $this->audit->record('auth.confirmed', $user, actor: $user);

        return redirect()->intended(route('dashboard.home'));
    }
}

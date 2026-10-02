<?php

namespace App\Http\Controllers\Dashboard\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\OwnerSession;
use App\Services\Auth\TwoFactor;
use App\Services\Core\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class TwoFactorChallengeController extends Controller
{
    public function __construct(private readonly OwnerSession $owner, private readonly TwoFactor $twoFactor, private readonly AuditLogger $audit) {}

    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->owner->pendingUser($request->session());
        if ($user === null || ! $user->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }

        return view('dashboard.auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->owner->pendingUser($request->session());
        if ($user === null || ! $user->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:12', 'required_without:recovery_code'],
            'recovery_code' => ['nullable', 'string', 'max:20'],
        ]);

        $key = "two-factor:{$user->id}";
        // Second window per account (FINAL-QA QA-008): the per-minute limit alone allows ~7,200 guesses a day; this one
        // stops the code step for the rest of the hour after a run of failures and leaves a trace in the audit log.
        $hourKey = "two-factor-hour:{$user->id}";
        /** @var int $max */
        $max = config('shelter.auth.max_attempts_per_minute');
        /** @var int $maxPerHour */
        $maxPerHour = config('shelter.auth.max_second_factor_failures_per_hour');
        foreach ([$key => $max, $hourKey => $maxPerHour] as $limiter => $limit) {
            if (RateLimiter::tooManyAttempts($limiter, $limit)) {
                throw ValidationException::withMessages(['code' => __('dashboard.auth.throttled', ['seconds' => RateLimiter::availableIn($limiter)])]);
            }
        }

        $recovery = is_string($data['recovery_code'] ?? null) && $data['recovery_code'] !== '';
        $ok = $recovery
            ? $this->twoFactor->useRecoveryCode($user, (string) $data['recovery_code'])
            : $this->twoFactor->verify($user, (string) ($data['code'] ?? ''));
        if (! $ok) {
            RateLimiter::hit($key, 60);
            RateLimiter::hit($hourKey, 3600);
            $this->audit->record(RateLimiter::tooManyAttempts($hourKey, $maxPerHour) ? 'auth.two_factor_locked' : 'auth.two_factor_failed', $user, actor: $user);
            throw ValidationException::withMessages([$recovery ? 'recovery_code' : 'code' => __('dashboard.auth.code_invalid')]);
        }

        RateLimiter::clear($key);
        RateLimiter::clear($hourKey);
        $this->owner->complete($request->session(), $user, $recovery ? 'recovery_code' : 'totp');

        return redirect()->intended(route('dashboard.home'));
    }
}

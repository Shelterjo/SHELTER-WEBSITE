<?php

namespace App\Http\Controllers\Dashboard\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\OwnerSession;
use App\Services\Auth\TwoFactor;
use App\Services\Core\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** First sign-in: the owner must enrol an authenticator app before any dashboard access. */
final class TwoFactorSetupController extends Controller
{
    public function __construct(private readonly OwnerSession $owner, private readonly TwoFactor $twoFactor, private readonly AuditLogger $audit) {}

    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->owner->pendingUser($request->session());
        if ($user === null || $user->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }
        $secret = $request->session()->get(OwnerSession::SETUP_SECRET);
        if (! is_string($secret)) {
            $secret = $this->twoFactor->generateSecret();
            $request->session()->put(OwnerSession::SETUP_SECRET, $secret);
        }

        return view('dashboard.auth.two-factor-setup', [
            'qr' => $this->twoFactor->qrSvg($user, $secret),
            'secret' => trim(chunk_split($secret, 4, ' ')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->owner->pendingUser($request->session());
        $secret = $request->session()->get(OwnerSession::SETUP_SECRET);
        if ($user === null || $user->hasTwoFactorEnabled() || ! is_string($secret)) {
            return redirect()->route('login');
        }
        $data = $request->validate(['code' => ['required', 'string', 'max:12']]);

        $step = $this->twoFactor->verifySecret($secret, $data['code']);
        if ($step === null) {
            throw ValidationException::withMessages(['code' => __('dashboard.auth.code_invalid')]);
        }

        $codes = $this->twoFactor->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_last_step' => $step,
            'two_factor_recovery_codes' => $codes['digests'],
        ])->save();
        $this->audit->record('auth.two_factor_enabled', $user, actor: $user);
        $this->owner->complete($request->session(), $user, 'totp_enrollment');
        $request->session()->flash('recovery_codes', $codes['plain']);

        return redirect()->route('dashboard.recovery-codes');
    }
}

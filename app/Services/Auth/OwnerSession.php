<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Core\AuditLogger;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Owner sign-in state machine (SECURITY-CENTER.md):
 *   password OK → "pending" (no access yet) → second factor (setup or challenge) → signed in.
 * The user is never authenticated before the second factor succeeds.
 */
final class OwnerSession
{
    public const PENDING = 'auth.pending';

    public const SETUP_SECRET = 'auth.setup_secret';

    public const LOGIN_AT = 'auth.login_at';

    public const CONFIRMED_AT = 'auth.confirmed_at';

    /** Dummy hash so a missing account costs the same time as a wrong password (no user enumeration). */
    private static ?string $dummyHash = null;

    public function __construct(private readonly AuditLogger $audit) {}

    public function checkPassword(string $email, string $password): ?User
    {
        $user = User::query()->where('email', mb_strtolower(trim($email)))->first();
        $valid = Hash::check($password, $user->password ?? (self::$dummyHash ??= Hash::make(Str::random(40))));

        return $valid && $user !== null && $user->isOwner() ? $user : null;
    }

    public function startPending(Session $session, User $user): void
    {
        $session->regenerate();
        $session->put(self::PENDING, ['id' => $user->id, 'at' => now()->getTimestamp()]);
    }

    public function pendingUser(Session $session): ?User
    {
        $pending = $session->get(self::PENDING);
        if (! is_array($pending) || ! is_int($pending['id'] ?? null) || ! is_int($pending['at'] ?? null)) {
            return null;
        }
        /** @var int $minutes */
        $minutes = config('shelter.auth.pending_lifetime');
        if (now()->getTimestamp() - $pending['at'] > $minutes * 60) {
            $session->forget([self::PENDING, self::SETUP_SECRET]);

            return null;
        }
        $user = User::query()->find($pending['id']);

        return $user !== null && $user->isOwner() ? $user : null;
    }

    public function complete(Session $session, User $user, string $method): void
    {
        $session->forget([self::PENDING, self::SETUP_SECRET]);
        Auth::guard('web')->login($user);
        $session->regenerate();
        $session->put(self::LOGIN_AT, now()->getTimestamp());
        $session->put(self::CONFIRMED_AT, now()->getTimestamp());
        $user->forceFill(['last_login_at' => now()])->save();
        $this->audit->record('auth.login', $user, meta: ['second_factor' => $method], actor: $user);
    }

    public function logout(Session $session, ?User $user, string $reason = 'logout'): void
    {
        if ($user !== null) {
            $this->audit->record('auth.logout', $user, meta: ['reason' => $reason], actor: $user);
        }
        Auth::guard('web')->logout();
        $session->invalidate();
        $session->regenerateToken();
    }

    public function isWithinAbsoluteLifetime(Session $session): bool
    {
        $loginAt = $session->get(self::LOGIN_AT);
        /** @var int $minutes */
        $minutes = config('shelter.auth.absolute_lifetime');

        return is_int($loginAt) && now()->getTimestamp() - $loginAt <= $minutes * 60;
    }

    public function recentlyConfirmed(Session $session): bool
    {
        $at = $session->get(self::CONFIRMED_AT);
        /** @var int $minutes */
        $minutes = config('shelter.auth.confirm_window');

        return is_int($at) && now()->getTimestamp() - $at <= $minutes * 60;
    }
}

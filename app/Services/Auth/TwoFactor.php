<?php

namespace App\Services\Auth;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP second factor (RFC 6238) + single-use recovery codes (SECURITY-CENTER.md).
 * - A code can be used once: the accepted time step is stored and older/equal steps are refused (no replay).
 * - Recovery codes are shown once and stored only as HMAC-SHA256 digests keyed with the app key.
 */
final class TwoFactor
{
    public function __construct(private readonly Google2FA $engine) {}

    public function generateSecret(): string
    {
        return $this->engine->generateSecretKey(32);
    }

    public function qrSvg(User $user, string $secret): string
    {
        /** @var string $issuer */
        $issuer = config('shelter.auth.totp_issuer');
        $url = $this->engine->getQRCodeUrl($issuer, $user->email, $secret);
        $svg = (new Writer(new ImageRenderer(new RendererStyle(200, 2), new SvgImageBackEnd)))->writeString($url);

        // Drop the XML prolog so the SVG can be inlined; the QR carries no user-controlled markup.
        return trim((string) preg_replace('/^<\?xml[^>]*>\s*/', '', $svg));
    }

    /** Enrollment check against a not-yet-saved secret. */
    public function verifySecret(string $secret, string $code): ?int
    {
        // A non-null old timestamp (0 = none yet) makes the library return the matched time step instead of true.
        $step = $this->engine->verifyKeyNewer($secret, self::digits($code), 0, $this->window());

        return is_int($step) ? $step : null;
    }

    /** Verifies and consumes the code's time step for this user (replay-safe). */
    public function verify(User $user, string $code): bool
    {
        if ($user->two_factor_secret === null) {
            return false;
        }
        $step = $this->engine->verifyKeyNewer($user->two_factor_secret, self::digits($code), $user->two_factor_last_step ?? 0, $this->window());
        if (! is_int($step)) {
            return false;
        }
        // Consumed with one conditional update (FINAL-QA QA-009): two parallel requests with the same code cannot both
        // pass — only the first moves the step forward.
        $consumed = User::query()->whereKey($user->getKey())
            ->where(fn ($query) => $query->whereNull('two_factor_last_step')->orWhere('two_factor_last_step', '<', $step))
            ->update(['two_factor_last_step' => $step]);
        if ($consumed !== 1) {
            return false;
        }
        $user->forceFill(['two_factor_last_step' => $step])->syncOriginalAttribute('two_factor_last_step');

        return true;
    }

    /** @return array{plain: list<string>, digests: list<string>} */
    public function generateRecoveryCodes(): array
    {
        /** @var int $count */
        $count = config('shelter.auth.recovery_codes');
        $plain = [];
        for ($i = 0; $i < $count; $i++) {
            $plain[] = Str::upper(Str::random(5).'-'.Str::random(5));
        }

        return ['plain' => $plain, 'digests' => array_map(self::digest(...), $plain)];
    }

    public function useRecoveryCode(User $user, string $code): bool
    {
        $digest = self::digest($code);
        $codes = $user->two_factor_recovery_codes ?? [];
        foreach ($codes as $i => $stored) {
            if (hash_equals($stored, $digest)) {
                unset($codes[$i]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }

    public static function digest(string $code): string
    {
        /** @var string $key */
        $key = config('app.key');

        return hash_hmac('sha256', Str::upper(trim($code)), $key);
    }

    private static function digits(string $code): string
    {
        return (string) preg_replace('/\D/', '', $code);
    }

    private function window(): int
    {
        /** @var int $window */
        $window = config('shelter.auth.totp_window');

        return $window;
    }
}

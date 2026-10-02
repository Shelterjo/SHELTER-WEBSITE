<?php

namespace App\Services\Forms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Throwable;

/**
 * Bot protection shared by every public form (RECRUITMENT-SECURITY §6, franchise field matrix): a signed time token
 * (minimum fill time, maximum age), a honeypot field and an idempotency key — no CAPTCHA, no third-party script.
 * The token counts from the FIRST time the form was opened and travels back after a validation error, so a person who
 * fixes one field and resends quickly is never taken for a bot.
 */
final class FormGuard
{
    public const HONEYPOT = 'website';

    public static function token(Request $request, int $maxAgeSeconds): string
    {
        $old = $request->old('form_token');
        if (is_string($old)) {
            $age = self::ageOf($old);
            if ($age !== null && $age >= 0 && $age <= $maxAgeSeconds) {
                return $old;
            }
        }

        return Crypt::encryptString((string) now()->getTimestamp());
    }

    public static function idempotencyKey(Request $request): string
    {
        $old = $request->old('idempotency_key');

        return is_string($old) && Str::isUuid($old) ? $old : (string) Str::uuid();
    }

    /** @return 'ok'|'bot'|'expired' */
    public static function check(Request $request, int $minSeconds, int $maxAgeSeconds): string
    {
        $age = self::ageOf((string) $request->input('form_token'));
        if (filled($request->input(self::HONEYPOT)) || ($age !== null && $age < $minSeconds)) {
            return 'bot';
        }

        return $age === null || $age > $maxAgeSeconds ? 'expired' : 'ok';
    }

    private static function ageOf(string $token): ?int
    {
        try {
            return now()->getTimestamp() - (int) Crypt::decryptString($token);
        } catch (Throwable) {
            return null;
        }
    }
}

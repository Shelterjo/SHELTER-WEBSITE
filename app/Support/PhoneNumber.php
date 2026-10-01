<?php

namespace App\Support;

/**
 * Display and link forms of an E.164 phone number (D-065): Arabic pages show the national form (0799009436),
 * English pages the grouped international form (+962 79 900 9436); tel:, WhatsApp and Schema always use the
 * international form. The grouping applies to 9-digit national significant numbers (2 · 3 · 4); anything else is
 * shown unspaced rather than guessed.
 */
final class PhoneNumber
{
    public static function display(string $e164, string $countryCode, string $locale): string
    {
        $digits = self::digits($e164);
        $national = str_starts_with($digits, $countryCode) ? substr($digits, strlen($countryCode)) : null;
        if ($national === null || $national === '') {
            return '+'.$digits;
        }
        if ($locale === 'ar') {
            return '0'.$national;
        }

        return strlen($national) === 9
            ? sprintf('+%s %s %s %s', $countryCode, substr($national, 0, 2), substr($national, 2, 3), substr($national, 5))
            : '+'.$digits;
    }

    public static function tel(string $e164): string
    {
        return 'tel:+'.self::digits($e164);
    }

    /** wa.me takes the international number without "+" (CONTACT-016). The pre-filled text waits for D-064. */
    public static function whatsapp(string $e164): string
    {
        return 'https://wa.me/'.self::digits($e164);
    }

    public static function international(string $e164): string
    {
        return '+'.self::digits($e164);
    }

    private static function digits(string $value): string
    {
        return (string) preg_replace('/\D+/', '', $value);
    }
}

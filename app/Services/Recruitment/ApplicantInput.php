<?php

namespace App\Services\Recruitment;

/**
 * Normalisation of applicant input — technical values only (RECRUITMENT-DATA-MODEL §4, M28 §04):
 * Arabic-Indic digits → Latin in phone, salary and ID numbers; phone cleaned (00 → +, local 07XXXXXXXX → +9627XXXXXXXX,
 * anything else kept as cleaned — no format is refused or forced); email lowercased for matching only. Free text is
 * never corrected, translated or refused for its script: only trimmed, spaces collapsed, invisible controls removed.
 */
final class ApplicantInput
{
    public static function digits(string $value): string
    {
        return strtr($value, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
    }

    /** Free text as written: trimmed, runs of spaces collapsed, invisible controls removed (line breaks kept when asked). */
    public static function text(string $value, bool $multiline = false): string
    {
        // Bidi and zero-width controls, other C0/C1 controls (tab and line breaks handled below).
        $value = (string) preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}\x{0000}-\x{0008}\x{000B}\x{000C}\x{000E}-\x{001F}\x{007F}-\x{009F}]/u', '', $value);
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        if (! $multiline) {
            return trim((string) preg_replace('/\s+/u', ' ', $value));
        }
        $lines = array_map(fn (string $line): string => trim((string) preg_replace('/[ \t\x{00A0}]+/u', ' ', $line)), explode("\n", $value));

        return trim((string) preg_replace("/\n{3,}/", "\n\n", implode("\n", $lines)));
    }

    /** The phone for matching and search; null when it is not a plausible phone number (7–15 digits). */
    public static function phone(string $raw): ?string
    {
        $value = (string) preg_replace('/[\s\-().\x{00A0}]/u', '', self::digits(trim($raw)));
        if (str_starts_with($value, '00')) {
            $value = '+'.substr($value, 2);
        }
        if (preg_match('/^07\d{8}$/', $value) === 1) {
            $value = '+962'.substr($value, 1);
        }

        return preg_match('/^\+?\d{7,15}$/', $value) === 1 ? $value : null;
    }

    public static function email(string $raw): string
    {
        return mb_strtolower(trim($raw));
    }

    /** National ID / document number: no spaces or dashes, Latin digits, uppercase letters (documents). */
    public static function identity(string $raw): string
    {
        return mb_strtoupper((string) preg_replace('/[\s\-\x{00A0}]/u', '', self::digits(trim($raw))));
    }

    /** Salary as typed ("450", "٤٥٠", "450.5"), or null when it is not a positive amount within the technical cap. */
    public static function salary(string $raw): ?string
    {
        $value = str_replace(['٫', ','], '.', self::digits(trim($raw)));
        if (preg_match('/^\d{1,5}(\.\d{1,2})?$/', $value) !== 1 || (float) $value <= 0) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }
}

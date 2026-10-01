<?php

namespace App\Support;

/**
 * CLDR plural category for a whole number (the same categories the browser's Intl.PluralRules returns), so the
 * server and resources/js/ui/open-status.ts pick the same string from one lang array.
 * Arabic: zero · one · two · few (3–10) · many (11–99) · other. English: one · other.
 */
final class PluralCategory
{
    public static function for(int $n, string $locale): string
    {
        if ($locale !== 'ar') {
            return $n === 1 ? 'one' : 'other';
        }
        $mod = $n % 100;

        return match (true) {
            $n === 0 => 'zero',
            $n === 1 => 'one',
            $n === 2 => 'two',
            $mod >= 3 && $mod <= 10 => 'few',
            $mod >= 11 => 'many',
            default => 'other',
        };
    }
}

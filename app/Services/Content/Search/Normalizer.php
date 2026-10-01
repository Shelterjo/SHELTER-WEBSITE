<?php

namespace App\Services\Content\Search;

use Normalizer as IntlNormalizer;

/**
 * Search normalisation and matching (GLOBAL-SEARCH §3, SRCH-006, Menu IA spec §8) — the PHP twin of
 * resources/js/menu/search.ts; both are tested against tests/fixtures/search-normalization.json (GS-T1):
 * diacritics and tatweel removed · أ/إ/آ/ٱ → ا · ة → ه · ى → ي · Arabic digits → Western · lowercase · symbols → space.
 * Matching: every query word starts some word of the item — an Arabic word is also tried without its definite article
 * ("فرنشايز" finds "الفرنشايز"); with `tolerant`, words of 5+ letters may differ by one letter (insertion, deletion or
 * substitution). No invented synonyms.
 */
final class Normalizer
{
    public static function normalize(string $text): string
    {
        $text = IntlNormalizer::normalize($text, IntlNormalizer::FORM_KC) ?: $text;
        $text = (string) preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u', '', $text);
        $text = str_replace(['أ', 'إ', 'آ', 'ٱ', 'ة', 'ى'], ['ا', 'ا', 'ا', 'ا', 'ه', 'ي'], $text);
        $text = (string) preg_replace_callback('/[\x{0660}-\x{0669}]/u', fn (array $d): string => (string) (mb_ord($d[0]) - 0x0660), $text);
        $text = (string) preg_replace_callback('/[\x{06F0}-\x{06F9}]/u', fn (array $d): string => (string) (mb_ord($d[0]) - 0x06F0), $text);
        $text = mb_strtolower($text);
        $text = (string) preg_replace('/[+()\-_\/.,:;\'"«»]/u', ' ', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /** @return list<string> */
    public static function words(string $text): array
    {
        $normalized = self::normalize($text);

        return $normalized === '' ? [] : explode(' ', $normalized);
    }

    /** True when a and b differ by at most one insertion, deletion or substitution. */
    public static function withinOneEdit(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }
        $x = mb_str_split($a);
        $y = mb_str_split($b);
        $lengthA = count($x);
        $lengthB = count($y);
        if (abs($lengthA - $lengthB) > 1) {
            return false;
        }
        $i = 0;
        $j = 0;
        $edits = 0;
        while ($i < $lengthA && $j < $lengthB) {
            if ($x[$i] === $y[$j]) {
                $i++;
                $j++;

                continue;
            }
            if (++$edits > 1) {
                return false;
            }
            if ($lengthA > $lengthB) {
                $i++;
            } elseif ($lengthA < $lengthB) {
                $j++;
            } else {
                $i++;
                $j++;
            }
        }

        return $edits + ($lengthA - $i) + ($lengthB - $j) <= 1;
    }

    /**
     * @param  list<string>  $tokens  normalized query words
     * @param  list<string>  $candidates  normalized words of the item
     */
    public static function tokensMatch(array $tokens, array $candidates, bool $tolerant = true): bool
    {
        $candidates = self::withoutArticle($candidates);
        foreach ($tokens as $token) {
            if (! self::tokenMatches($token, $candidates, $tolerant)) {
                return false;
            }
        }

        return true;
    }

    /** @param  list<string>  $terms */
    public static function matches(string $query, array $terms): bool
    {
        $tokens = self::words($query);
        if ($tokens === []) {
            return true;
        }

        return self::tokensMatch($tokens, array_merge(...array_map(self::words(...), $terms ?: [''])));
    }

    /**
     * Each word, plus the same word without a leading "ال" when something is left after it.
     *
     * @param  list<string>  $words
     * @return list<string>
     */
    private static function withoutArticle(array $words): array
    {
        $out = $words;
        foreach ($words as $word) {
            if (str_starts_with($word, 'ال') && mb_strlen($word) > 3) {
                $out[] = mb_substr($word, 2);
            }
        }

        return $out;
    }

    /** @param  list<string>  $candidates */
    private static function tokenMatches(string $token, array $candidates, bool $tolerant): bool
    {
        foreach ($candidates as $word) {
            if (str_starts_with($word, $token)) {
                return true;
            }
        }
        if (! $tolerant || mb_strlen($token) < 5) {
            return false;
        }
        foreach ($candidates as $word) {
            // Same rule as the menu: the whole word, or its same-length start (typing in progress).
            if (self::withinOneEdit($token, $word) || self::withinOneEdit($token, mb_substr($word, 0, mb_strlen($token)))) {
                return true;
            }
        }

        return false;
    }
}

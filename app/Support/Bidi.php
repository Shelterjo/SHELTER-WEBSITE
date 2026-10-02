<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Mixed-direction text, handled at render time only — the stored (approved) wording never changes (UX-006 DR-07,
 * DR-17):
 * - dir(): the direction of a text whose language differs from the page's (an Arabic product name on the English
 *   menu), so its brackets and punctuation stay in place.
 * - isolate(): on an Arabic page, every run of Latin words inside an Arabic text (SHELTER COFFEE DRIVE, V60) becomes
 *   its own left-to-right island (<bdi>), so a line break or the punctuation next to it cannot reorder it. A short
 *   run — a name — also stays on one line; a long Latin passage still wraps. Text without Arabic letters is only
 *   escaped.
 */
final class Bidi
{
    /** Latin words joined by single spaces; inner ’ ' . - & are part of a word, a final full stop or comma is not. */
    private const LATIN_RUN = '/[A-Za-z0-9](?:[A-Za-z0-9&’\'.\-]*[A-Za-z0-9])?(?: [A-Za-z0-9](?:[A-Za-z0-9&’\'.\-]*[A-Za-z0-9])?)*/u';

    /** A run this short (a brand or branch name) never breaks across lines. */
    private const KEEP_WORDS = 3;

    private const KEEP_CHARS = 24;

    public static function dir(?string $lang): ?string
    {
        return match ($lang) {
            'ar' => 'rtl',
            'en' => 'ltr',
            default => null,
        };
    }

    public static function isolate(?string $text, ?string $locale = null): HtmlString
    {
        $text ??= '';
        if (($locale ?? app()->getLocale()) !== 'ar' || preg_match('/\p{Arabic}/u', $text) !== 1) {
            return new HtmlString(e($text));
        }

        $html = '';
        $offset = 0;
        preg_match_all(self::LATIN_RUN, $text, $runs, PREG_OFFSET_CAPTURE);
        foreach ($runs[0] as [$run, $at]) {
            if (preg_match('/[A-Za-z]/', $run) !== 1) {
                continue; // a number alone (2019) sits fine in Arabic text
            }
            $html .= e(substr($text, $offset, $at - $offset));
            $keep = substr_count($run, ' ') < self::KEEP_WORDS && mb_strlen($run) <= self::KEEP_CHARS;
            $html .= '<bdi dir="ltr" lang="en"'.($keep ? ' class="ui-nowrap"' : '').'>'.e($run).'</bdi>';
            $offset = $at + strlen($run);
        }

        return new HtmlString($html.e(substr($text, $offset)));
    }
}

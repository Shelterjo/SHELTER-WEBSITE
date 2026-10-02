<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * One text value from a request, or the default when the value is missing or not text (FINAL-QA QA-026). A plain
 * (string) cast turns ?q[]=x into "Array to string conversion" — a 500 instead of an empty search.
 */
final class Input
{
    /** From the query string or the body. */
    public static function text(Request $request, string $key, string $default = ''): string
    {
        $value = $request->input($key, $default);

        return is_string($value) || is_int($value) || is_float($value) ? (string) $value : $default;
    }

    /** From the query string only. */
    public static function query(Request $request, string $key, string $default = ''): string
    {
        $value = $request->query($key, $default);

        return is_string($value) ? $value : $default;
    }
}

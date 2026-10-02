<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Whether a dashboard GET came from the Owner's own navigation (FINAL-QA QA-011). A few screens record something on
 * GET — opening an application marks it as seen, a page size is remembered — and a session cookie travels with a
 * top-level link from another site (SameSite=Lax). Such a visit still shows the screen, it just records nothing.
 * Browsers that send no Sec-Fetch-Site header are treated as own navigation.
 */
final class OwnNavigation
{
    public static function is(Request $request): bool
    {
        return $request->headers->get('Sec-Fetch-Site') !== 'cross-site';
    }
}

<?php

namespace App\View\Composers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Error pages take their language from the URL prefix (/ar/… → Arabic, /en/… → English), never from the browser
 * (D-067). Any other path (the root, unknown prefixes) gets a bilingual page, Arabic first (SI-S01).
 */
final class ErrorPageLocale
{
    public function __construct(private readonly Request $request) {}

    public function compose(View $view): void
    {
        $prefix = $this->request->segment(1);
        /** @var list<string> $locales */
        $locales = config('shelter.locales');
        $locale = is_string($prefix) && in_array($prefix, $locales, true) ? $prefix : null;

        app()->setLocale($locale ?? 'ar');
        $view->with([
            'errorLocale' => $locale,
            'bilingual' => $locale === null,
            'dir' => ($locale ?? 'ar') === 'ar' ? 'rtl' : 'ltr',
        ]);
    }
}

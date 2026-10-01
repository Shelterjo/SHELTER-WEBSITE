<?php

namespace App\View\Composers;

use App\Services\Site\ContactActions;
use App\Services\Site\Markets;
use App\Support\PageUrl;
use App\Support\SiteLinks;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data for the public site chrome (layouts.site → x-ui.site-header / x-ui.site-footer, DS-018): navigation to the
 * pages that exist, the language switch, and the approved footer contacts (CONTACT-015: one number + WhatsApp).
 * A navigation item appears only when its route exists, so pages join the header as they ship.
 */
final class SiteChrome
{
    /** Primary navigation in IA order (NAV-006: Menu, then Locations). route name => lang key. */
    private const NAV = [
        'menu' => 'site.nav.menu',
        'locations' => 'site.nav.locations',
    ];

    public function __construct(
        private readonly Markets $markets,
        private readonly ContactActions $contacts,
        private readonly Request $request,
    ) {}

    public function compose(View $view): void
    {
        $locale = app()->getLocale();
        $parameters = ['locale' => $locale, 'market' => $this->markets->current()?->code];

        $nav = [];
        foreach (self::NAV as $route => $label) {
            $href = $parameters['market'] !== null ? SiteLinks::to($route, $parameters) : null;
            if ($href !== null) {
                $nav[] = ['label' => (string) __($label), 'href' => $href, 'current' => $this->current($route)];
            }
        }

        /** @var array<string, string> $alternates */
        $alternates = $view->getData()['alternates'] ?? [];
        $languages = [];
        /** @var list<string> $locales */
        $locales = config('shelter.locales');
        foreach ($locales as $code) {
            $languages[] = [
                'locale' => $code,
                'label' => (string) __('site.language.'.$code),
                'href' => $alternates[$code] ?? PageUrl::route('home', ['locale' => $code]),
                'current' => $code === $locale,
            ];
        }

        $view->with('siteChrome', [
            'home' => PageUrl::route('home', ['locale' => $locale]),
            'nav' => $nav,
            'languages' => $languages,
            'phone' => $this->contacts->phone($locale),
            'whatsapp' => $this->contacts->whatsapp($locale),
            'contact' => SiteLinks::to('contact', $parameters),
        ]);
    }

    /** aria-current: "page" on the page itself, "true" on its section (a branch page inside Locations). */
    private function current(string $route): ?string
    {
        return match (true) {
            $this->request->routeIs($route) => 'page',
            $this->request->routeIs($route.'.*') => 'true',
            default => null,
        };
    }
}

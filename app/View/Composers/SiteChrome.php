<?php

namespace App\View\Composers;

use App\Services\Content\Awards;
use App\Services\Content\Pages;
use App\Services\Content\Team;
use App\Services\Experiences\Events;
use App\Services\Experiences\Placements;
use App\Services\Site\ContactActions;
use App\Services\Site\Markets;
use App\Services\Site\SocialLinks;
use App\Support\PageUrl;
use App\Support\SiteLinks;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data for the public site chrome (layouts.site → x-ui.site-header / x-ui.site-footer, DS-018): navigation to the
 * pages that exist, the language switch, and the approved footer contacts (CONTACT-015: one number + WhatsApp).
 * A navigation item appears only when its route exists, so pages join the header as they ship. Content pages (About,
 * FAQ, Privacy, Terms, Franchise, Media Center) are linked from the footer only while they are published
 * (App\Services\Content\Pages), Events only while there is an event to show, Awards and SHELTER Family only while a
 * verified award / a consenting member exists, and Careers once its route exists; the header stays Menu + Locations
 * (D-027).
 */
final class SiteChrome
{
    /** Primary navigation in IA order (NAV-006: Menu, then Locations). route name => lang key. */
    /** Platform names as the platforms write them (proper nouns, shown in Latin script in both languages). */
    private const SOCIAL_NAMES = ['instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'snapchat' => 'Snapchat',
        'youtube' => 'YouTube', 'x' => 'X', 'linkedin' => 'LinkedIn'];

    private const NAV = [
        'menu' => 'site.nav.menu',
        'locations' => 'site.nav.locations',
    ];

    public function __construct(
        private readonly Markets $markets,
        private readonly ContactActions $contacts,
        private readonly SocialLinks $social,
        private readonly Pages $pages,
        private readonly Events $events,
        private readonly Awards $awards,
        private readonly Team $team,
        private readonly Placements $placements,
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

        // The language switch keeps the visitor's context (?branch, ?q) through `languageLinks`; the hreflang tags in
        // <head> stay on the clean `alternates` (FINAL-QA QA-016).
        /** @var array<string, string> $alternates */
        $alternates = $view->getData()['languageLinks'] ?? $view->getData()['alternates'] ?? [];
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

        $withCurrent = fn (array $link): array => ['label' => $link['label'], 'href' => $link['href'], 'current' => $this->current($link['key'])];
        $market = $this->markets->current();
        $eventsHref = $market !== null ? SiteLinks::to('events', $parameters) : null;
        $events = $market !== null && $eventsHref !== null && $this->events->listed($market, $locale) !== []
            ? [['label' => (string) __('site.events.title'), 'href' => $eventsHref, 'key' => 'events']]
            : [];

        // One query for the footer pages (each labelled with its approved page name); Careers sits before the business pages.
        $pageLinks = $this->pages->links([...Pages::EXPLORE, ...Pages::BUSINESS], $locale);
        $isBusiness = fn (array $link): bool => in_array($link['key'], Pages::BUSINESS, true);
        $careersHref = SiteLinks::to('careers', $parameters);
        $explore = [
            ...array_filter($pageLinks, fn (array $link): bool => ! $isBusiness($link)),
            ...($careersHref !== null ? [['label' => (string) __('site.nav.careers'), 'href' => $careersHref, 'key' => 'careers']] : []),
            ...array_filter($pageLinks, $isBusiness),
        ];
        // Awards and SHELTER Family join only while something verified / consented is there to show.
        foreach (['awards' => [$this->awards->published($locale) !== [], 'awards.title'], 'family' => [$this->team->published($locale) !== [], 'family.title']] as $route => [$hasContent, $label]) {
            $href = $hasContent ? SiteLinks::to($route, $parameters) : null;
            if ($href !== null) {
                $explore[] = ['label' => (string) __($label), 'href' => $href, 'key' => $route];
            }
        }

        $view->with('siteChrome', [
            'home' => PageUrl::route('home', ['locale' => $locale]),
            'nav' => $nav,
            'footerNav' => [...$nav, ...array_map($withCurrent, [...$events, ...$explore])],
            'legal' => array_map($withCurrent, $this->pages->links(Pages::LEGAL, $locale)),
            'languages' => $languages,
            'phone' => $this->contacts->phone($locale),
            'whatsapp' => $this->contacts->whatsapp($locale),
            'contact' => SiteLinks::to('contact', $parameters),
            'social' => array_map(fn (string $platform, string $url): array => ['label' => self::SOCIAL_NAMES[$platform] ?? $platform, 'href' => $url],
                array_keys($social = $this->social->published()), $social),
            'search' => SiteLinks::to('search', $parameters),
            // The top announcement bar: the one experience the engine picks now, or nothing (DX-010, DX-012).
            'announcement' => ($market = $this->markets->current()) !== null ? $this->placements->current($market, Placements::TOP_BAR, $locale) : null,
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

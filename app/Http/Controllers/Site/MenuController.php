<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Services\Menu\Page\MenuItem;
use App\Services\Menu\Page\MenuPage;
use App\Services\Menu\Page\MenuSection;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * The menu (/ar/jo/menu/ — SI-M02, Menu IA spec): one page, no product pages. `?branch=drive|house` selects a branch
 * context on the same page; the canonical is always the clean URL (spec §1, §12). Search text never enters the URL.
 */
final class MenuController extends Controller
{
    public function __invoke(Request $request, Market $market, MenuPage $page): View
    {
        $locale = app()->getLocale();
        $branch = $request->query('branch');
        $menu = $page->build($market, $locale, is_string($branch) ? $branch : null);
        $canonical = PageUrl::route('menu');
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => (string) __('menu.title'), 'href' => $canonical],
        ];

        // Search index embedded in the page (spec §8, ~4 KB gzipped): approved names to show, extra terms to match.
        $index = array_map(fn (MenuItem $item): array => [
            'id' => $item->anchor(),
            'name' => $item->name,
            'lang' => $item->nameLang,
            'secondary' => $item->secondary,
            'secondaryLang' => $item->secondaryLang,
            'section' => $item->location,
            'price' => $item->priceFils,
            'terms' => $item->searchTerms,
        ], $menu->items());

        return view('site.menu', [
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates('menu'),
            'menu' => $menu,
            'market' => $market,
            'crumbs' => $crumbs,
            'searchIndex' => $index,
            'jsonLd' => [StructuredData::breadcrumbs($crumbs), $this->menuSchema($menu->allSections(), $canonical, $locale)],
        ]);
    }

    /**
     * schema.org Menu with sections and items: approved names and VAT-inclusive JOD prices only (spec §13).
     *
     * @param  list<MenuSection>  $sections
     * @return array<string, mixed>
     */
    private function menuSchema(array $sections, string $url, string $locale): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Menu',
            'name' => (string) __('menu.title'),
            'url' => $url,
            'inLanguage' => $locale,
            'hasMenuSection' => array_map(fn (MenuSection $section): array => [
                '@type' => 'MenuSection',
                'name' => $section->name,
                'hasMenuItem' => array_map(fn (MenuItem $item): array => [
                    '@type' => 'MenuItem',
                    'name' => $item->name,
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => number_format($item->priceFils / 1000, 2, '.', ''),
                        'priceCurrency' => $item->currency,
                    ],
                ], $section->items()),
            ], $sections),
        ];
    }
}

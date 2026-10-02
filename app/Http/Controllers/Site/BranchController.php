<?php

namespace App\Http\Controllers\Site;

use App\Enums\ContactKind;
use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Services\MasterData\MasterData;
use App\Services\Site\BranchDirectory;
use App\Support\PageUrl;
use App\Support\PhoneNumber;
use App\Support\SiteLinks;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/**
 * Branch page (/ar/jo/locations/irbid/drive/ — SI-M05/M06, fixed slugs D-053): approved name, weekly hours with
 * today marked, live state, the address and Maps link once the Owner saves them (PO-010), services and payments said
 * yes, call/WhatsApp/Directions (mobile action bar, D-061), BreadcrumbList + CafeOrCoffeeShop JSON-LD (address and
 * geo only when approved).
 */
final class BranchController extends Controller
{
    public function __invoke(Market $market, BranchDirectory $directory, MasterData $data, string $city, string $branch): View
    {
        $locale = app()->getLocale();
        $summary = $directory->find($market, $city, $branch, $locale);
        abort_if($summary === null, 404);

        $parameters = ['city' => $city, 'branch' => $branch];
        $canonical = PageUrl::route('locations.branch', $parameters);
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => (string) __('site.locations.title'), 'href' => PageUrl::route('locations')],
            ['label' => $summary->name, 'href' => $canonical],
        ];
        $phone = $data->contact(ContactKind::PhoneMain);
        $menuUrl = SiteLinks::to('menu', ['locale' => $locale, 'market' => $market->code]);

        return view('site.branch', [
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates('locations.branch', $parameters),
            'market' => $market,
            'branch' => $summary,
            'crumbs' => $crumbs,
            // BRANCH-025: the menu opens filtered to this branch (?branch=drive), when the Menu page exists.
            'menuUrl' => $menuUrl !== null ? $menuUrl.'?branch='.rawurlencode($summary->branch->slug) : null,
            'jsonLd' => [
                StructuredData::breadcrumbs($crumbs),
                StructuredData::cafe(
                    $summary,
                    $phone?->show_on_branch_cards === true && $phone->value !== null ? PhoneNumber::international($phone->value) : null,
                    asset('brand/logo-white-480.png'),
                    PageUrl::route('gateway'),
                    $menuUrl,
                ),
            ],
        ]);
    }
}

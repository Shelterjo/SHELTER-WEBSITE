<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Services\Site\BranchDirectory;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/** Branches of the market (/ar/jo/locations/ — SI-M03): approved names, today's hours, live state, call/WhatsApp. */
final class LocationsController extends Controller
{
    public function __invoke(Market $market, BranchDirectory $directory): View
    {
        $locale = app()->getLocale();
        $canonical = PageUrl::route('locations');
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => (string) __('site.locations.title'), 'href' => $canonical],
        ];

        return view('site.locations', [
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates('locations'),
            'market' => $market,
            'branches' => $directory->forMarket($market, $locale),
            'crumbs' => $crumbs,
            'jsonLd' => [StructuredData::breadcrumbs($crumbs)],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\MasterData\MasterData;
use App\Services\Site\BranchDirectory;
use App\Services\Site\Markets;
use App\Support\PageUrl;
use App\Support\SiteLinks;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/**
 * Localized home (/ar/, /en/ — SI-B02): brand hero (typography only: no approved photos, MEDIA PENDING OWNER
 * APPROVAL) with the two top visitor actions (D-013, NAV-006: Menu, then Locations), then the branches with today's
 * hours and live open state. Copy is functional DRAFT wording until the homepage copy options (D-068).
 */
final class HomeController extends Controller
{
    public function __invoke(Markets $markets, BranchDirectory $directory, MasterData $data): View
    {
        $locale = app()->getLocale();
        $market = $markets->current();
        $parameters = ['locale' => $locale, 'market' => $market?->code];
        $founded = $data->setting('brand.founded_year');

        return view('site.home', [
            'canonical' => PageUrl::route('home'),
            'alternates' => PageUrl::alternates('home'),
            'branches' => $market !== null ? $directory->forMarket($market, $locale) : [],
            'menuUrl' => $market !== null ? SiteLinks::to('menu', $parameters) : null,
            'locationsUrl' => $market !== null ? SiteLinks::to('locations', $parameters) : null,
            'jsonLd' => [StructuredData::organization(
                'SHELTER COFFEE', 'شلتر كوفي', PageUrl::route('gateway'), asset('brand/logo-white-480.png'),
                is_int($founded) ? ['foundingDate' => (string) $founded] : [],
            )],
        ]);
    }
}

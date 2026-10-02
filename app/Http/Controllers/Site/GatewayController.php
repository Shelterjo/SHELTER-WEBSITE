<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\MasterData\MasterData;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/**
 * Root brand gateway (D-052, D-066, D-067): bilingual, no automatic redirect; the two language doors, hreflang
 * (x-default = this page) and the brand entity.
 */
final class GatewayController extends Controller
{
    public function __invoke(MasterData $data): View
    {
        app()->setLocale('ar');
        $founded = $data->setting('brand.founded_year');

        return view('site.gateway', [
            'dir' => 'rtl',
            'canonical' => PageUrl::route('gateway'),
            'alternates' => PageUrl::alternates('home') + ['x-default' => PageUrl::route('gateway')],
            // SITE-INVENTORY SI-B01: the brand entity, the same as on the home pages (FINAL-QA QA-020).
            'jsonLd' => [StructuredData::organization(
                'SHELTER COFFEE', 'شلتر كوفي', PageUrl::route('gateway'), asset('brand/logo-white-480.png'),
                is_int($founded) ? ['foundingDate' => (string) $founded] : [],
            ), StructuredData::website('SHELTER COFFEE', 'شلتر كوفي', PageUrl::route('gateway'), ['ar', 'en'])],
        ]);
    }
}

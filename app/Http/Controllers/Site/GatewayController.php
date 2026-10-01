<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\PageUrl;
use Illuminate\Contracts\View\View;

/**
 * Root brand gateway (D-052, D-066, D-067): bilingual, no automatic redirect. Content arrives in PHASE 2;
 * PHASE 1 provides the route, language links and hreflang (x-default = this page).
 */
final class GatewayController extends Controller
{
    public function __invoke(): View
    {
        app()->setLocale('ar');

        return view('site.gateway', [
            'dir' => 'rtl',
            'canonical' => PageUrl::route('gateway'),
            'alternates' => PageUrl::alternates('home') + ['x-default' => PageUrl::route('gateway')],
        ]);
    }
}

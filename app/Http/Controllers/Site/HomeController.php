<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\PageUrl;
use Illuminate\Contracts\View\View;

/** Localized home (/ar/, /en/). PHASE 1 shell; sections come from pages/page_sections in PHASE 2. */
final class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('site.home', [
            'canonical' => PageUrl::route('home'),
            'alternates' => PageUrl::alternates('home'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\SeoHealth;
use Illuminate\Contracts\View\View;

/** Local SEO health (M57 §48): what Google can read about SHELTER today and what is missing — read-only. */
final class SeoController extends Controller
{
    public function __invoke(SeoHealth $health): View
    {
        return view('dashboard.seo', [
            'branches' => $health->branches(app()->getLocale()),
            'texts' => $health->texts(),
            'arabicMenu' => $health->arabicMenu(),
            'indexable' => $health->indexable(),
        ]);
    }
}

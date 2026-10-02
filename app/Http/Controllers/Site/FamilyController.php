<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Content\Team;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/**
 * SHELTER Family (SI-B15, DX-002…006): the team members who agreed to appear, as they agreed (G13-TF-01). Nobody
 * published with consent → 404, no empty page. WebPage + BreadcrumbList only — no Person data about employees.
 */
final class FamilyController extends Controller
{
    public function __invoke(Team $team): View
    {
        $locale = app()->getLocale();
        $members = $team->published($locale);
        abort_if($members === [], 404);

        $canonical = PageUrl::route('family');
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => (string) __('family.title'), 'href' => $canonical],
        ];

        return view('site.family', [
            'members' => $members,
            'crumbs' => $crumbs,
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates('family'),
            'jsonLd' => [
                StructuredData::breadcrumbs($crumbs),
                ['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => (string) __('family.title'), 'url' => $canonical, 'inLanguage' => $locale],
            ],
        ]);
    }
}

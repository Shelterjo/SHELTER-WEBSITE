<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Content\Team;
use App\Services\Experiences\Recognitions;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/**
 * SHELTER Family (SI-B15, DX-002…006): the team members who agreed to appear, as they agreed (G13-TF-01). Nobody
 * published with consent → 404, no empty page. WebPage + BreadcrumbList only — no Person data about employees. Above the
 * team: the Employee of the Month when the Owner placed it here (DX-009) — only someone already shown on this page.
 */
final class FamilyController extends Controller
{
    public function __invoke(Team $team, Recognitions $recognitions): View
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
            'recognition' => $recognitions->current('family', $locale),
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

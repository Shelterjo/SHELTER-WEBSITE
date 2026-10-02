<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Content\Awards;
use App\Services\Content\Pages;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/**
 * Awards (SI-B14, inside the Media Center — M35 §35): only verified awards (Fact Registry, PO-032), newest first.
 * No verified award → 404, no empty page. WebPage + BreadcrumbList only (no Review or Rating data).
 */
final class AwardsController extends Controller
{
    public function __invoke(Awards $awards, Pages $pages): View
    {
        $locale = app()->getLocale();
        $list = $awards->published($locale);
        abort_if($list === [], 404);

        $canonical = PageUrl::route('awards');
        $mediaCenter = $pages->published('media', $locale);
        $crumbs = [['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')]];
        if ($mediaCenter !== null) {
            $crumbs[] = ['label' => $mediaCenter->name, 'href' => PageUrl::route('media')];
        }
        $crumbs[] = ['label' => (string) __('awards.title'), 'href' => $canonical];

        return view('site.awards', [
            'awards' => $list,
            'crumbs' => $crumbs,
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates('awards'),
            'jsonLd' => [
                StructuredData::breadcrumbs($crumbs),
                ['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => (string) __('awards.title'), 'url' => $canonical, 'inLanguage' => $locale],
            ],
        ]);
    }
}

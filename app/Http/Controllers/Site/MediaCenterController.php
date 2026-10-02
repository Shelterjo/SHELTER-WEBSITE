<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Services\Content\Awards;
use App\Services\Content\Pages;
use App\Services\Experiences\Recognitions;
use App\Services\MasterData\MasterData;
use App\Services\Media\MediaImage;
use App\Services\Media\MediaLibrary;
use App\Services\Site\BranchDirectory;
use App\Services\Site\Markets;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/**
 * Media Center + Press Kit (SI-B13, MEDIA-RIGHTS §5): a curated page, not a second library. 404 until the Owner
 * publishes its page (G14-PO-01 → PO-062); then it shows the page's own text, the approved key facts (official names,
 * founding year, branches by approved name), press photos that pass MediaRights (press_kit usages of this page),
 * the latest verified awards (PO-032) — and nothing that is MISSING or PENDING: no story until PO-017, no logo until
 * the brand files, no press contact until the Owner names one (never an invented email). The Employee of the Month
 * shows here only when the Owner placed it here (DX-009) and its person and photo may appear (Recognitions).
 */
final class MediaCenterController extends Controller
{
    public function __invoke(Pages $pages, Awards $awards, MediaLibrary $library, Markets $markets, BranchDirectory $directory, MasterData $data, Recognitions $recognitions): View
    {
        $locale = app()->getLocale();
        $page = $pages->published('media', $locale);
        abort_if($page === null, 404);

        $facts = [
            ['label' => (string) __('press.facts.name_en'), 'value' => (string) __('site.brand', [], 'en'), 'lang' => 'en'],
            ['label' => (string) __('press.facts.name_ar'), 'value' => (string) __('site.brand_ar', [], 'ar'), 'lang' => 'ar'],
        ];
        $founded = $data->setting('brand.founded_year');
        if (is_int($founded)) {
            $facts[] = ['label' => (string) __('press.facts.founded'), 'value' => (string) $founded, 'lang' => null];
        }
        $market = $markets->current();
        $branches = $market !== null ? array_map(fn ($b): string => $b->name, $directory->forMarket($market, $locale)) : [];
        if ($branches !== []) {
            $facts[] = ['label' => (string) __('press.facts.branches'), 'value' => implode(' · ', $branches), 'lang' => null];
        }

        $pageId = Page::query()->where('key', 'media')->value('id');
        $photos = MediaUsage::query()->with('media')->where('usable_type', Page::class)->where('usable_id', $pageId)
            ->where('slot', 'press_kit')->where('channel', 'website')->orderBy('sort')->orderBy('id')->get()
            ->map(fn (MediaUsage $usage): ?MediaImage => $library->image($usage->media, $locale))->filter()->values()->all();

        $canonical = PageUrl::route('media');
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => $page->name, 'href' => $canonical],
        ];
        $allAwards = $awards->published($locale);

        return view('site.media-center', [
            'page' => $page,
            'facts' => $facts,
            'photos' => $photos,
            'highlights' => array_slice($allAwards, 0, 3),
            'recognition' => $recognitions->current('media', $locale),
            'awardsUrl' => $allAwards !== [] ? PageUrl::route('awards') : null,
            'crumbs' => $crumbs,
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates('media'),
            'description' => $page->description,
            'jsonLd' => [
                StructuredData::breadcrumbs($crumbs),
                ['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $page->name, 'url' => $canonical, 'inLanguage' => $locale],
            ],
        ]);
    }
}

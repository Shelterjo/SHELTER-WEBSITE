<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Indexing;
use App\Services\Site\BranchDirectory;
use App\Services\Site\Markets;
use App\Support\PageUrl;
use App\Support\SiteLinks;
use Illuminate\Http\Response;

/**
 * /sitemap.xml (SI-S05, SEO-029): canonical, public, indexable pages only, each with its hreflang alternates.
 * Exists only where the site is indexable (production + SHELTER_INDEXING); everywhere else it is a 404.
 * One urlset for now (a few dozen URLs); a sitemap index per language joins when the page count needs it.
 */
final class SitemapController extends Controller
{
    public function __invoke(Markets $markets, BranchDirectory $directory): Response
    {
        abort_unless(Indexing::siteIndexable(), 404);

        /** @var list<string> $locales */
        $locales = config('shelter.locales');
        $gateway = PageUrl::route('gateway');
        // Each cluster = the language versions of one page; every URL in it is listed with all of them (SEO-032).
        $clusters = [PageUrl::alternates('home') + ['x-default' => $gateway]];

        $market = $markets->current();
        if ($market !== null) {
            foreach (['menu', 'locations'] as $route) {
                $cluster = [];
                foreach ($locales as $locale) {
                    $url = SiteLinks::to($route, ['locale' => $locale, 'market' => $market->code]);
                    if ($url !== null) {
                        $cluster[$locale] = $url;
                    }
                }
                $clusters[] = $cluster;
            }
            $branches = [];
            foreach ($locales as $locale) {
                foreach ($directory->forMarket($market, $locale) as $branch) {
                    $branches[$branch->branch->code][$locale] = $branch->url;
                }
            }
            array_push($clusters, ...array_values($branches));
        }

        return response()->view('site.sitemap', ['clusters' => array_values(array_filter($clusters))], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}

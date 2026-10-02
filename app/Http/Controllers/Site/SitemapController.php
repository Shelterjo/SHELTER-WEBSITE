<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Indexing;
use App\Services\Content\Awards;
use App\Services\Content\Pages;
use App\Services\Content\Team;
use App\Services\Experiences\Events;
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
    public function __invoke(Markets $markets, BranchDirectory $directory, Pages $pages, Events $events, Awards $awards, Team $team): Response
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
            // Events listing only while something is listed (an empty listing is noindex).
            if ($events->listed($market, 'ar') !== [] || $events->listed($market, 'en') !== []) {
                $clusters[] = $this->cluster('events', $locales, ['market' => $market->code]);
            }
        }

        // Brand pages only once published (both languages are required to publish): content pages, franchise, Media Center.
        foreach ([...Pages::EXPLORE, ...Pages::LEGAL, ...Pages::BUSINESS] as $key) {
            if ($pages->published($key, 'ar') !== null && $pages->published($key, 'en') !== null) {
                $clusters[] = $this->cluster($key, $locales);
            }
        }
        $clusters[] = $this->cluster('careers', $locales);
        if ($awards->published('en') !== []) {
            $clusters[] = $this->cluster('awards', $locales);
        }
        if ($team->published('en') !== []) {
            $clusters[] = $this->cluster('family', $locales);
        }

        return response()->view('site.sitemap', ['clusters' => array_values(array_filter($clusters))], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * @param  list<string>  $locales
     * @param  array<string, string>  $parameters
     * @return array<string, string>
     */
    private function cluster(string $route, array $locales, array $parameters = []): array
    {
        $cluster = [];
        foreach ($locales as $locale) {
            $url = SiteLinks::to($route, ['locale' => $locale] + $parameters);
            if ($url !== null) {
                $cluster[$locale] = $url;
            }
        }

        return $cluster;
    }
}

<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Content\Search\Search;
use App\Services\Content\Search\SearchLog;
use App\Support\Input;
use App\Support\PageUrl;
use App\Support\PluralCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Site search results (/ar/search/?q= — SI-B08, GLOBAL-SEARCH §4): server-rendered, works without JavaScript,
 * `noindex, follow` and out of the sitemap (SEO-023). Scope PUBLIC is fixed here on the server. Results are grouped
 * (menu · branches · pages · questions); no results → a message and the main pages, never an empty screen.
 * The query is counted anonymously only when the search log is switched on (PO-019).
 */
final class SearchController extends Controller
{
    public function __invoke(Request $request, Search $search, SearchLog $log): View
    {
        $locale = app()->getLocale();
        $query = mb_substr(trim(Input::query($request, 'q', '')), 0, Search::MAX_QUERY);
        $results = $query === '' ? null : $search->search($query, $locale);
        if ($results !== null) {
            $log->record('site', $locale, $query, $results->total);
        }
        $alternates = PageUrl::alternates('search');
        $languageLinks = [];
        foreach ($alternates as $code => $url) {
            $languageLinks[$code] = $query === '' ? $url : $url.'?'.http_build_query(['q' => $query]);
        }

        return view('site.search', [
            'canonical' => PageUrl::route('search'),
            'alternates' => $alternates,
            'languageLinks' => $languageLinks, // the switch keeps the query; hreflang stays clean (FINAL-QA QA-016)
            'noindexFollow' => true,
            'query' => $query,
            'results' => $results,
            'countText' => $results === null ? null : (string) __('site.search.results.'.($results->total === 0 ? 'zero' : PluralCategory::for($results->total, $locale)), ['count' => $results->total]),
        ]);
    }
}

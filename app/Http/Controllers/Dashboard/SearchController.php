<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\OwnerSearch;
use App\Support\Input;
use App\Support\PluralCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Dashboard search (DASH-019): a plain GET form (works without JavaScript) and this results page, grouped by area,
 * each result linking to its screen. The dashboard script refreshes the results while typing (only the results area is
 * replaced). Owner only (routes/dashboard.php); the words searched for are not stored or logged.
 */
final class SearchController extends Controller
{
    public function __invoke(Request $request, OwnerSearch $search): View
    {
        $locale = app()->getLocale();
        $query = OwnerSearch::clean(Input::query($request, 'q'));
        $ready = mb_strlen($query) >= OwnerSearch::MIN;
        $groups = $ready ? $search->search($query, $locale) : [];
        $total = array_sum(array_column($groups, 'total'));

        return view('dashboard.search', [
            'query' => $query,
            'ready' => $ready,
            'groups' => $groups,
            'countText' => $ready ? (string) __('dashboard.search.results.'.($total === 0 ? 'zero' : PluralCategory::for($total, $locale)), ['count' => $total]) : null,
        ]);
    }
}

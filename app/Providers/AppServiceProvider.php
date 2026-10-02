<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Experience;
use App\Models\MenuCategory;
use App\Models\Product;
use App\Models\SearchAlias;
use App\Services\Content\Awards;
use App\Services\Content\Pages;
use App\Services\Content\Search\SearchFreshness;
use App\Services\Content\Team;
use App\Services\Forms\FormGuard;
use App\Services\Site\Markets;
use App\View\Composers\DashboardChrome;
use App\View\Composers\ErrorPageLocale;
use App\View\Composers\SiteChrome;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One market lookup per request for every component that needs it (header, footer, branches).
        $this->app->scoped(Markets::class);
        $this->app->scoped(Pages::class);
        $this->app->scoped(Awards::class);
        $this->app->scoped(Team::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Public site chrome (header, footer) and localized error pages (SITE-INVENTORY §D).
        View::composer('layouts.site', SiteChrome::class);
        View::composer('layouts.dashboard', DashboardChrome::class);
        View::composer(['errors.404', 'errors::404'], ErrorPageLocale::class);

        // Public search (SEC-007): 30 searches a minute per address — counted on a keyed hash, the address is not kept.
        RateLimiter::for('search', fn (Request $request): Limit => Limit::perMinute(30)->by(FormGuard::clientKey($request)));

        // A saved change to anything the search shows marks the derived index as changed (rebuilt on the next search).
        $changed = function (): void {
            app(SearchFreshness::class)->invalidate();
        };
        Product::saved($changed);
        MenuCategory::saved($changed);
        SearchAlias::saved($changed);
        Experience::saved($changed);
        Branch::saved($changed);
    }
}

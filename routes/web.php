<?php

use App\Http\Controllers\Site\BranchController;
use App\Http\Controllers\Site\GatewayController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\LocationsController;
use App\Http\Controllers\Site\RobotsController;
use App\Http\Controllers\Site\SitemapController;
use Illuminate\Support\Facades\Route;

/*
| Public URL architecture (D-031, D-052, docs/platform/SITE-INVENTORY.md):
|   /                      brand gateway (x-default), no geo/IP redirect (D-067)
|   /{ar|en}/              brand layer
|   /{ar|en}/{market}/…    market layer (jo) — menu, locations, branches, events (PHASE 2+)
| Every public URL ends with a slash (CanonicalTrailingSlash). The dashboard lives under /dashboard (routes/dashboard.php).
*/

Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/', GatewayController::class)->name('gateway');

Route::prefix('{locale}')->where(['locale' => 'ar|en'])->middleware('locale')->group(function (): void {
    Route::get('/', HomeController::class)->name('home');

    // Market layer: locations and branch pages (SI-M03, SI-M05/M06). Branch slugs are fixed (D-053); the city and
    // market pages themselves stay reserved (URL-07), so /ar/jo/ and /ar/jo/locations/irbid/ have no route (404).
    Route::prefix('{market}')->where(['market' => '[a-z]{2}'])->middleware('market')->group(function (): void {
        Route::get('locations/', LocationsController::class)->name('locations');
        Route::get('locations/{city}/{branch}/', BranchController::class)
            ->where(['city' => '[a-z0-9-]+', 'branch' => '[a-z0-9-]+'])
            ->name('locations.branch');
    });
});

require __DIR__.'/dashboard.php';

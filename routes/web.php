<?php

use App\Http\Controllers\Site\AwardsController;
use App\Http\Controllers\Site\BranchController;
use App\Http\Controllers\Site\CareersController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\ContentPageController;
use App\Http\Controllers\Site\EventsController;
use App\Http\Controllers\Site\FamilyController;
use App\Http\Controllers\Site\FeedbackController;
use App\Http\Controllers\Site\FranchiseController;
use App\Http\Controllers\Site\GatewayController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\LlmsController;
use App\Http\Controllers\Site\LocationsController;
use App\Http\Controllers\Site\MediaCenterController;
use App\Http\Controllers\Site\MenuController;
use App\Http\Controllers\Site\RobotsController;
use App\Http\Controllers\Site\SearchController;
use App\Http\Controllers\Site\ShaltoorController;
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
Route::get('/llms.txt', LlmsController::class)->name('llms');
Route::get('/', GatewayController::class)->name('gateway');

Route::prefix('{locale}')->where(['locale' => 'ar|en'])->middleware('locale')->group(function (): void {
    Route::get('/', HomeController::class)->name('home');
    Route::get('contact/', ContactController::class)->name('contact');
    // Brand content pages (SI-B03, B05, B06, B07): 404 until their text is published in both languages.
    foreach (['about', 'faq', 'privacy', 'terms'] as $page) {
        Route::get($page.'/', ContentPageController::class)->defaults('key', $page)->name($page);
    }
    // Careers (SI-B11): content in both languages; THE application form is Arabic only (CAREERS-REQUIREMENTS §2).
    Route::get('careers/', [CareersController::class, 'show'])->name('careers');
    Route::post('careers/', [CareersController::class, 'submit'])->name('careers.submit');
    Route::post('careers/uploads/', [CareersController::class, 'upload'])->name('careers.upload');
    Route::delete('careers/uploads/{attachment}/', [CareersController::class, 'removeUpload'])->whereNumber('attachment')->name('careers.upload.remove');
    Route::get('careers/submitted/', [CareersController::class, 'submitted'])->name('careers.submitted');
    Route::match(['get', 'post'], 'careers/track/', [CareersController::class, 'track'])->name('careers.track');
    // Franchise & partnerships (SI-B12): 404 until its content is published (PO-030); the FR form needs its approved texts.
    Route::get('franchise/', [FranchiseController::class, 'show'])->name('franchise');
    Route::post('franchise/', [FranchiseController::class, 'submit'])->name('franchise.submit');
    Route::get('franchise/submitted/', [FranchiseController::class, 'submitted'])->name('franchise.submitted');
    // Media Center + Press Kit (SI-B13), Awards (SI-B14), SHELTER Family (SI-B15): 404 until approved content exists.
    Route::get('media/', MediaCenterController::class)->name('media');
    Route::get('awards/', AwardsController::class)->name('awards');
    Route::get('family/', FamilyController::class)->name('family');
    // Customer feedback (SI-B16, VOICE-OF-CUSTOMER): anonymous, noindex, linked from nowhere until PO-063; closed in production.
    Route::get('feedback/', [FeedbackController::class, 'show'])->name('feedback');
    Route::post('feedback/', [FeedbackController::class, 'submit'])->name('feedback.submit');
    Route::get('feedback/submitted/', [FeedbackController::class, 'submitted'])->name('feedback.submitted');
    // Site search (SI-B08): noindex, rate-limited (SEC-007 — the address only counts toward the limit, hashed).
    Route::get('search/', SearchController::class)->middleware('throttle:search')->name('search');
    // شلتور / Shaltoor (M69): one question → one answer from public data (JSON, rate-limited, 404 when switched off).
    Route::post('shaltoor/', ShaltoorController::class)->name('shaltoor');

    // Market layer: locations and branch pages (SI-M03, SI-M05/M06). Branch slugs are fixed (D-053); the city and
    // market pages themselves stay reserved (URL-07), so /ar/jo/ and /ar/jo/locations/irbid/ have no route (404).
    Route::prefix('{market}')->where(['market' => '[a-z]{2}'])->middleware('market')->group(function (): void {
        Route::get('menu/', MenuController::class)->name('menu');
        Route::get('locations/', LocationsController::class)->name('locations');
        Route::get('locations/{city}/{branch}/', BranchController::class)
            ->where(['city' => '[a-z0-9-]+', 'branch' => '[a-z0-9-]+'])
            ->name('locations.branch');
        // Events and campaigns (SI-M07, SI-M08 — DX-014).
        Route::get('events/', [EventsController::class, 'index'])->name('events');
        Route::get('events/{slug}/', [EventsController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('events.show');
    });
});

require __DIR__.'/dashboard.php';

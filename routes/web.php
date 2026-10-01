<?php

use App\Http\Controllers\Site\GatewayController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\RobotsController;
use Illuminate\Support\Facades\Route;

/*
| Public URL architecture (D-031, D-052, docs/platform/SITE-INVENTORY.md):
|   /                      brand gateway (x-default), no geo/IP redirect (D-067)
|   /{ar|en}/              brand layer
|   /{ar|en}/{market}/…    market layer (jo) — menu, locations, branches, events (PHASE 2+)
| Every public URL ends with a slash (CanonicalTrailingSlash). The dashboard lives under /dashboard (routes/dashboard.php).
*/

Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/', GatewayController::class)->name('gateway');

Route::prefix('{locale}')->where(['locale' => 'ar|en'])->middleware('locale')->group(function (): void {
    Route::get('/', HomeController::class)->name('home');
});

require __DIR__.'/dashboard.php';

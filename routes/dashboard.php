<?php

use App\Http\Controllers\Dashboard\Auth\ConfirmIdentityController;
use App\Http\Controllers\Dashboard\Auth\LoginController;
use App\Http\Controllers\Dashboard\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Dashboard\Auth\TwoFactorSetupController;
use App\Http\Controllers\Dashboard\Content\AwardsController;
use App\Http\Controllers\Dashboard\Content\MediaController;
use App\Http\Controllers\Dashboard\Content\PagesController;
use App\Http\Controllers\Dashboard\Content\TeamController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\RecoveryCodesController;
use App\Http\Middleware\DashboardLocale;
use Illuminate\Support\Facades\Route;

/*
| Owner dashboard routes (PHASE 1: authentication shell; modules from PHASE 3).
| Every route except the sign-in steps is behind auth + owner (active, second factor, absolute lifetime).
| No registration and no password-reset-by-email route exist (owner accounts are created on the server).
*/

Route::prefix('dashboard')->middleware(DashboardLocale::class)->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
        Route::get('two-factor', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
        Route::post('two-factor', [TwoFactorChallengeController::class, 'store'])->name('two-factor.verify');
        Route::get('two-factor/setup', [TwoFactorSetupController::class, 'show'])->name('two-factor.setup');
        Route::post('two-factor/setup', [TwoFactorSetupController::class, 'store'])->name('two-factor.enable');
    });

    Route::middleware(['auth', 'owner'])->name('dashboard.')->group(function (): void {
        Route::get('/', HomeController::class)->name('home');

        // Content (FINAL-ARCHITECTURE-REVIEW §10) — no code needed to change the site (M30, M50).
        Route::get('content/pages', [PagesController::class, 'index'])->name('pages.index');
        Route::get('content/pages/{key}', [PagesController::class, 'edit'])->name('pages.edit');
        Route::put('content/pages/{key}', [PagesController::class, 'update'])->name('pages.update');
        Route::get('content/media', [MediaController::class, 'index'])->name('media.index');
        Route::get('content/media/upload', [MediaController::class, 'create'])->name('media.create');
        Route::post('content/media', [MediaController::class, 'store'])->name('media.store');
        Route::whereNumber('media')->group(function (): void {
            Route::get('content/media/{media}', [MediaController::class, 'edit'])->name('media.edit');
            Route::put('content/media/{media}', [MediaController::class, 'update'])->name('media.update');
            Route::get('content/media/{media}/preview', [MediaController::class, 'preview'])->name('media.preview');
            Route::post('content/media/{media}/archive', [MediaController::class, 'archive'])->name('media.archive');
            Route::post('content/media/{media}/restore', [MediaController::class, 'restore'])->name('media.restore');
        });
        Route::get('content/awards', [AwardsController::class, 'index'])->name('awards.index');
        Route::get('content/awards/new', [AwardsController::class, 'create'])->name('awards.create');
        Route::post('content/awards', [AwardsController::class, 'store'])->name('awards.store');
        Route::whereNumber('award')->group(function (): void {
            Route::get('content/awards/{award}', [AwardsController::class, 'edit'])->name('awards.edit');
            Route::put('content/awards/{award}', [AwardsController::class, 'update'])->name('awards.update');
            Route::post('content/awards/{award}/archive', [AwardsController::class, 'archive'])->name('awards.archive');
            Route::post('content/awards/{award}/restore', [AwardsController::class, 'restore'])->name('awards.restore');
        });
        Route::get('content/team', [TeamController::class, 'index'])->name('team.index');
        Route::get('content/team/new', [TeamController::class, 'create'])->name('team.create');
        Route::post('content/team', [TeamController::class, 'store'])->name('team.store');
        Route::whereNumber('member')->group(function (): void {
            Route::get('content/team/{member}', [TeamController::class, 'edit'])->name('team.edit');
            Route::put('content/team/{member}', [TeamController::class, 'update'])->name('team.update');
            Route::post('content/team/{member}/archive', [TeamController::class, 'archive'])->name('team.archive');
            Route::post('content/team/{member}/restore', [TeamController::class, 'restore'])->name('team.restore');
        });
        Route::get('recovery-codes', RecoveryCodesController::class)->name('recovery-codes');
        Route::get('confirm', [ConfirmIdentityController::class, 'show'])->name('confirm');
        Route::post('confirm', [ConfirmIdentityController::class, 'store'])->name('confirm.store');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    });
});

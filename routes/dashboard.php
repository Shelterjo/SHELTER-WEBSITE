<?php

use App\Http\Controllers\Dashboard\Auth\ConfirmIdentityController;
use App\Http\Controllers\Dashboard\Auth\LoginController;
use App\Http\Controllers\Dashboard\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Dashboard\Auth\TwoFactorSetupController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\RecoveryCodesController;
use Illuminate\Support\Facades\Route;

/*
| Owner dashboard routes (PHASE 1: authentication shell; modules from PHASE 3).
| Every route except the sign-in steps is behind auth + owner (active, second factor, absolute lifetime).
| No registration and no password-reset-by-email route exist (owner accounts are created on the server).
*/

Route::prefix('dashboard')->group(function (): void {
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
        Route::get('recovery-codes', RecoveryCodesController::class)->name('recovery-codes');
        Route::get('confirm', [ConfirmIdentityController::class, 'show'])->name('confirm');
        Route::post('confirm', [ConfirmIdentityController::class, 'store'])->name('confirm.store');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    });
});

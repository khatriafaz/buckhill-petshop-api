<?php

/**
 * All routes added into this file
 * will have /api prefix
 */

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->middleware(['throttle:api'])->group(function () {
    Route::prefix('user')->as('user.')->group(function() {
        Route::post('/login', [LoginController::class, 'login'])->name('login');
        Route::post('/create', [RegisterController::class, 'store'])->name('create');

        Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/reset-password-token', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');

        /**
         * Auth protected routes
         */
        Route::middleware(['auth:api'])->group(function () {
            Route::get('/', [UserProfileController::class, 'show'])->name('profile');
            Route::put('/edit', [UserProfileController::class, 'update'])->name('profile');
            Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
            Route::delete('/', [UserProfileController::class, 'destroy'])->name('destroy');
        });
    });
});

<?php

/**
 * All routes added into this file
 * will have /api prefix
 */

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProductController;
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

    Route::prefix('file')->as('files.')->middleware(['auth:api'])->group(function() {
        Route::post('/upload', [FileController::class, 'store'])->name('store');
        Route::get('/{file:uuid}', [FileController::class, 'show'])->name('show');
    });

    Route::as('categories.')->middleware(['auth:api'])->group(function() {
        Route::get('/categories', [CategoryController::class, 'index'])->name('index');
        Route::get('/category/{category:uuid}', [CategoryController::class, 'show'])->name('show');
        Route::post('/category/create', [CategoryController::class, 'store'])->name('store');
        Route::put('/category/{category:uuid}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/category/{category:uuid}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    Route::as('products.')->middleware(['auth:api'])->group(function() {
        Route::post('/product/create', [ProductController::class, 'store'])->name('store');
        Route::put('/product/{product:uuid}', [ProductController::class, 'update'])->name('update');
        Route::delete('/product/{product:uuid}', [ProductController::class, 'destroy'])->name('destroy');
    });
});

<?php

/**
 * All routes added into this file
 * will have /api prefix
 */

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['throttle:api'])->group(function () {
    // TODO add user routes here
});

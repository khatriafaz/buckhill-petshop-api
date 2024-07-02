<?php

use Illuminate\Support\Facades\Route;

Route::get('api/documentation', function () {
    return view('swagger.index');
});

Route::get('swagger.json', function () {
    return response()->file(public_path('swagger.json'));
});

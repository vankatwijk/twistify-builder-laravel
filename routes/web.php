<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstasitePreviewController;
use App\Http\Controllers\TenantSiteController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test/{host}/{path?}', [InstasitePreviewController::class, 'show'])
    ->where([
        'host' => '[a-z0-9\.-]+',
        'path' => '.*',
    ])
    ->name('instasite.preview');

// Catch-all for tenant domains (must be LAST)
Route::get('/{path?}', [TenantSiteController::class, 'serve'])
    ->where('path', '.*');

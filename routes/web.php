<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\InstasitePreviewController;
use App\Http\Controllers\TenantSiteController;

Route::get('/', function (Request $request, TenantSiteController $tenantSiteController) {
    $builderHost = strtolower(config('instasites.builder_host', 'builder.twistify.io'));
    $host = strtolower($request->getHost());

    if ($host === $builderHost) {
        return view('welcome');
    }

    return $tenantSiteController->serve($request, '');
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

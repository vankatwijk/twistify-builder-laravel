<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Instasites\BuildController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'app' => config('app.name', 'laravel'),
        'env' => app()->environment(),
        'time' => now()->toIso8601String(),
    ]);
});

Route::middleware('builder.key')->group(function () {
  Route::post('/build', [BuildController::class, 'build']); // POST /api/build
  Route::post('/reset', [BuildController::class, 'reset']); // POST /api/reset
});
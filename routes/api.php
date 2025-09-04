<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Instasites\BuildController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('builder.key')->group(function () {
  Route::post('/build', [BuildController::class, 'build']); // POST /api/build
  Route::post('/reset', [BuildController::class, 'reset']); // POST /api/reset
});
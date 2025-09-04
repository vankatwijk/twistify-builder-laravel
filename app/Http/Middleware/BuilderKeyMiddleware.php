<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class BuilderKeyMiddleware {
  public function handle(Request $request, Closure $next) {
    $expected = config('instasites.builder_api_key');
    if (!$expected) return $next($request); // auth disabled
    if ($request->header('X-Builder-Key') !== $expected) {
      return response()->json(['ok'=>false,'error'=>'Unauthorized'], 401);
    }
    return $next($request);
  }
}
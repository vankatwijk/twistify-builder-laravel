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
  Route::post('/upsert-post', [BuildController::class,'upsertPost']); // NEW

  Route::get('/debug-host', function (Request $request) {
      $normalizeHost = function (string $value): string {
          $value = strtolower(trim($value));

          if (str_contains($value, '://')) {
              $parsed = parse_url($value, PHP_URL_HOST);
              if (is_string($parsed) && $parsed !== '') {
                  $value = $parsed;
              }
          }

          $value = preg_replace('/:\\d+$/', '', $value) ?? $value;

          return rtrim($value, '.');
      };

      $hostInput = (string) $request->query('host', $request->getHost());
      $host = $normalizeHost($hostInput);

      $hostCandidates = [$host];
      if (str_starts_with($host, 'www.')) {
          $hostCandidates[] = substr($host, 4);
      } else {
          $hostCandidates[] = 'www.' . $host;
      }
      $hostCandidates = array_values(array_unique(array_filter($hostCandidates)));

      $sitesRoot = rtrim((string) config('instasites.sites_root', ''), '/');
      $paths = [];
      $resolved = null;

      foreach ($hostCandidates as $candidateHost) {
          $path = $sitesRoot . '/' . $candidateHost . '/public';
          $exists = is_dir($path);
          $paths[] = ['host' => $candidateHost, 'path' => $path, 'exists' => $exists];
          if ($exists && $resolved === null) {
              $resolved = $path;
          }
      }

      return response()->json([
          'ok' => true,
          'input' => $hostInput,
          'normalized_host' => $host,
          'builder_host' => $normalizeHost((string) config('instasites.builder_host', 'builder.twistify.io')),
          'sites_root' => $sitesRoot,
          'host_candidates' => $hostCandidates,
          'checks' => $paths,
          'resolved_public_path' => $resolved,
      ]);
  });

});
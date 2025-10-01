<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class BuilderKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Only enforce when a key is configured
        $configured = (string) env('BUILDER_API_KEY', '');

        if ($configured !== '') {
            $incoming = $request->header('X-Builder-Key'); // exact header
            if ($incoming !== $configured) {
                return response()->json(['ok' => false, 'error' => 'Unauthorized'], 401);
            }
        }

        return $next($request);
    }
}
<?php

// app/Http/Controllers/InstasitePreviewController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Mime\MimeTypes;

class InstasitePreviewController extends Controller
{
    public function show(Request $req, string $host, ?string $path = null)
    {
        $root   = rtrim(config('instasites.sites_root'), '/')."/{$host}/public";
        $path   = ltrim($path ?? '', '/');

        // Directory index fallback
        $candidate = $path === '' ? 'index.html' : $path;
        $full      = is_dir("{$root}/{$candidate}") ? "{$root}/{$candidate}/index.html" : "{$root}/{$candidate}";

        if (!File::exists($full)) {
            abort(404, 'Preview file not found.');
        }

        // Guess mime
        $ext  = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mime = (new MimeTypes())->getMimeTypes($ext)[0] ?? 'application/octet-stream';

        // For HTML, inject <base> and rewrite root-absolute URLs
        if (in_array($ext, ['html', 'htm'])) {
            $html = File::get($full);

            // 1) Inject <base> (nice for purely relative links)
            if (stripos($html, '<base ') === false) {
                $base = url("/test/{$host}/");
                $html = preg_replace(
                    '/<head([^>]*)>/i',
                    '<head$1>' . "\n" . '  <base href="' . rtrim($base, '/') . '/">' . "\n",
                    $html,
                    1
                );
            }

            // 2) Rewrite root-absolute href/src => prefix with /test/{host}
            $prefix = rtrim(url("/test/{$host}"), '/');

            // href="/..."/src="/..."  (but not //)
            $html = preg_replace_callback(
                '/\s(href|src)=([\'"])(\/(?!\/)[^\'"]*)\2/i',
                function ($m) use ($prefix) {
                    // $m[3] starts with "/"
                    return ' ' . $m[1] . '=' . $m[2] . $prefix . $m[3] . $m[2];
                },
                $html
            );

            // srcset="/img 1x, /img@2x 2x"  -> prefix each URL starting with /
            $html = preg_replace_callback(
                '/\ssrcset=([\'"])([^\'"]+)\1/i',
                function ($m) use ($prefix) {
                    $parts = array_map('trim', explode(',', $m[2]));
                    foreach ($parts as &$p) {
                        // split "URL descriptor" (e.g., "/img 2x")
                        $chunks = preg_split('/\s+/', $p, 2);
                        if (!empty($chunks[0]) && str_starts_with($chunks[0], '/') && !str_starts_with($chunks[0], '//')) {
                            $chunks[0] = $prefix . $chunks[0];
                            $p = implode(' ', $chunks);
                        }
                    }
                    return ' srcset="' . implode(', ', $parts) . '"';
                },
                $html
            );

            // 3) Fix malformed language switcher links (e.g., https://domain/api/build?lang=xx -> /test/{host}/xx/)
            $html = preg_replace_callback(
                '/\shref=([\'"])(?:https?:\/\/[^\/]+)?\/api\/build\?lang=([a-z]{2,3})\1/i',
                function ($m) use ($prefix) {
                    $lang = $m[2];
                    return ' href=' . $m[1] . $prefix . '/' . $lang . '/' . $m[1];
                },
                $html
            );

            //     '<link rel="canonical" href="' . $prefix . '/">', $html, 1);

            return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        // Binary/asset passthrough
        $contents = File::get($full);
        return response($contents, 200, ['Content-Type' => $mime]);
    }
}

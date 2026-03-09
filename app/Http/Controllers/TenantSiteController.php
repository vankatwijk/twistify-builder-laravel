<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Mime\MimeTypes;

class TenantSiteController extends Controller
{
    public function serve(Request $request, ?string $path = null)
    {
        $builderHost = $this->normalizeHost((string) config('instasites.builder_host', 'builder.twistify.io'));
        $host = $this->normalizeHost($request->getHost());

        // Builder host should be handled by normal Laravel routes/controllers
        if ($host === $builderHost) {
            abort(404);
        }

        // Basic host hardening
        // - allow a-z 0-9 . -
        // - forbid ".."
        if (!preg_match('/^[a-z0-9.-]+$/', $host) || str_contains($host, '..')) {
            abort(400, 'Invalid host');
        }

        $sitesRoot = rtrim(config('instasites.sites_root', env('SITES_ROOT', '/opt/bitnami/apache/htdocs/sites')), '/');
        $hostCandidates = $this->hostCandidates($host);
        $root = null;

        foreach ($hostCandidates as $candidateHost) {
            $candidateRoot = $sitesRoot . '/' . $candidateHost . '/public';
            if (is_dir($candidateRoot)) {
                $root = $candidateRoot;
                break;
            }
        }

        if ($root === null) {
            abort(404, 'Site folder not found.');
        }

        // Request path (Laravel gives us "" for "/")
        $path = ltrim($path ?? '', '/');
        $candidate = $path === '' ? 'index.html' : $path;

        // If the candidate is a directory, serve its index.html
        $full = is_dir($root . '/' . $candidate)
            ? ($root . '/' . $candidate . '/index.html')
            : ($root . '/' . $candidate);

        // If not found and URL has no extension, try SPA-ish fallback to /index.html
        // (This helps when static sites use client-side routing)
        if (!File::exists($full)) {
            $hasExtension = str_contains(basename($candidate), '.');
            if (!$hasExtension && File::exists($root . '/index.html')) {
                $full = $root . '/index.html';
            } else {
                abort(404);
            }
        }

        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mime = (new MimeTypes())->getMimeTypes($ext)[0] ?? 'application/octet-stream';

        // Return file (simple, no caching logic for now)
        return response(File::get($full), 200, ['Content-Type' => $mime]);
    }

    private function normalizeHost(string $value): string
    {
        $value = strtolower(trim($value));

        if (str_contains($value, '://')) {
            $parsed = parse_url($value, PHP_URL_HOST);
            if (is_string($parsed) && $parsed !== '') {
                $value = $parsed;
            }
        }

        $value = preg_replace('/:\d+$/', '', $value) ?? $value;
        $value = rtrim($value, '.');

        return $value;
    }

    /**
     * @return list<string>
     */
    private function hostCandidates(string $host): array
    {
        $candidates = [$host];

        if (str_starts_with($host, 'www.')) {
            $candidates[] = substr($host, 4);
        } else {
            $candidates[] = 'www.' . $host;
        }

        return array_values(array_unique(array_filter($candidates)));
    }
}

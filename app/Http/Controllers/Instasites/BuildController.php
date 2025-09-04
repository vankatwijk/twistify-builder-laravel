<?php

namespace App\Http\Controllers\Instasites;

use App\Http\Controllers\Controller;
use App\Services\Instasites\SiteBuilderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildController extends Controller
{
  public function __construct(private SiteBuilderService $builder) {}

  public function build(Request $r){
    $data = $this->validatePayload($r->all());

    $host = $data['dns']['hostname']
      ?? $data['blueprint']['primary_domain']
      ?? null;

    if (!$host) return response()->json(['ok'=>false,'error'=>'hostname required'], 422);

    // Build immediately (sync). If you prefer queues later, just dispatch a job here.
    $result = $this->builder->build($host, $data);

    return response()->json([
      'ok'             => true,
      'job_id'         => $result['job_id'],
      'hostname'       => $host,
      'pages'          => $result['pages_count'],
      'posts'          => $result['posts_count'],
      'theme'          => $result['manifest']['theme'],
      'locales'        => $result['manifest']['locales'],
      'default_locale' => $result['manifest']['default_locale'],
    ]);
  }

  public function reset(Request $r){
    $host = $r->input('hostname');
    if (!$host) return response()->json(['ok'=>false,'error'=>'hostname required'], 422);

    $deleted = $this->builder->reset($host);
    return response()->json(['ok'=>true,'message'=>"Deleted {$host}", 'deleted'=>$deleted]);
  }

    private function validatePayload(array $in){
        return \Illuminate\Support\Facades\Validator::make($in, [
            // --- blueprint ---
            'blueprint.site_name'       => 'required|string',
            'blueprint.primary_domain'  => 'required|string',
            'blueprint.default_locale'  => 'nullable|string',
            'blueprint.theme'           => 'nullable|array',
            'blueprint.theme.name'      => 'nullable|string',
            'blueprint.theme.logoText'  => 'nullable|string',
            'blueprint.theme.primaryColor' => 'nullable|string',
            'blueprint.theme.accentColor'  => 'nullable|string',
            'blueprint.theme.font'      => 'nullable|string',
            'blueprint.theme.nav'       => 'nullable|array',
            'blueprint.settings'        => 'nullable|array',

            // --- locales ---
            'locales'                   => 'nullable|array',

            // --- pages ---
            'pages'                     => 'nullable|array',
            'pages.*.slug'              => 'required_with:pages|string',
            'pages.*.title'             => 'nullable|string',
            'pages.*.html'              => 'nullable|string',          // <— KEEP THE HTML
            'pages.*.meta_title'        => 'nullable|string',
            'pages.*.meta_description'  => 'nullable|string',
            'pages.*.media_links'       => 'nullable|array',
            'pages.*.locale'            => 'nullable|string',

            // --- posts ---
            'posts'                     => 'nullable|array',
            'posts.*.slug'              => 'required_with:posts|string',
            'posts.*.title'             => 'nullable|string',
            'posts.*.html'              => 'nullable|string',          // <— KEEP THE HTML
            'posts.*.meta_title'        => 'nullable|string',
            'posts.*.meta_description'  => 'nullable|string',
            'posts.*.author'            => 'nullable|string',
            'posts.*.category'          => 'nullable|string',
            'posts.*.published_at'      => 'nullable|string',
            'posts.*.media_links'       => 'nullable|array',
            'posts.*.locale'            => 'nullable|string',

            // --- dns / misc ---
            'dns'                       => 'nullable|array',
            'dns.hostname'              => 'nullable|string',
            'reset'                     => 'nullable|boolean',
        ])->validate();
    }
}

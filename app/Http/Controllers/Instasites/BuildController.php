<?php

namespace App\Http\Controllers\Instasites;

use App\Http\Controllers\Controller;
use App\Services\Instasites\SiteBuilderService;
use Illuminate\Http\Request;

class BuildController extends Controller
{
  public function __construct(private SiteBuilderService $builder) {}

  public function build(Request $r){
    $data = $this->validatePayload($r->all());

    $host = $data['dns']['hostname']
      ?? $data['blueprint']['primary_domain']
      ?? null;

    if (!$host) return response()->json(['ok'=>false,'error'=>'hostname required'], 422);

    // --- Single-locale build support ---
    $lang = $r->query('lang');
    if ($lang) {
      $lang = strtolower($lang);
      // Filter locales
      $data['locales'] = array_values(array_filter(($data['locales'] ?? []), fn($l) => strtolower($l) === $lang));
      // Filter pages
      if (isset($data['pages'])) {
        $data['pages'] = array_values(array_filter($data['pages'], fn($p) => strtolower($p['locale'] ?? '') === $lang));
      }
      // Filter posts
      if (isset($data['posts'])) {
        $data['posts'] = array_values(array_filter($data['posts'], fn($p) => strtolower($p['locale'] ?? '') === $lang));
      }
      // Also update default_locale if needed
      if (!empty($data['locales'])) {
        $data['blueprint']['default_locale'] = $data['locales'][0];
      }
    }

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
          'blueprint.theme.logoUrl'   => 'nullable|string|url',
          'blueprint.theme.primaryColor' => 'nullable|string',
          'blueprint.theme.accentColor'  => 'nullable|string',
          'blueprint.theme.font'      => 'nullable|string',
          'blueprint.theme.nav'       => 'nullable|array',
          'blueprint.theme.nav.includeBlog' => 'nullable',
          'blueprint.theme.nav.items'  => 'nullable|array',
          'blueprint.theme.nav.items.*.title' => 'nullable|string',
          'blueprint.theme.nav.items.*.slug' => 'nullable|string',
          'blueprint.theme.nav.cta' => 'nullable|array',
          'blueprint.theme.nav.cta.enabled' => 'nullable',
          'blueprint.theme.nav.cta.text' => 'nullable|string',
          'blueprint.theme.nav.cta.href' => 'nullable|string',
          'blueprint.theme.nav.cta.style' => 'nullable|string',
          'blueprint.theme.nav.cta.newTab' => 'nullable',
          'blueprint.theme.hero'      => 'nullable|array',
          'blueprint.theme.hero.imageUrl' => 'nullable|string|url',
          'blueprint.theme.hero.headline' => 'nullable|string',
          'blueprint.theme.hero.subheadline' => 'nullable|string',
          'blueprint.theme.hero.ctaText' => 'nullable|string',
          'blueprint.theme.hero.ctaHref' => 'nullable|string',
          'blueprint.theme.hero.secondaryCtaText' => 'nullable|string',
          'blueprint.theme.hero.secondaryCtaHref' => 'nullable|string',
          'blueprint.theme.hero.overlayOpacity' => 'nullable|numeric|min:0|max:1',
          'blueprint.theme.trust' => 'nullable|array',
          'blueprint.theme.trust.enabled' => 'nullable',
          'blueprint.theme.trust.ratingText' => 'nullable|string',
          'blueprint.theme.trust.statsText' => 'nullable|string',
          'blueprint.theme.trust.logos' => 'nullable|array',
          'blueprint.theme.trust.logos.*' => 'nullable|string|url',
          'blueprint.theme.footer' => 'nullable|array',
          'blueprint.theme.footer.compliance' => 'nullable|array',
          'blueprint.theme.footer.compliance.show18Plus' => 'nullable',
          'blueprint.theme.footer.compliance.disclaimerText' => 'nullable|string',
          'blueprint.theme.footer.compliance.responsibleLink' => 'nullable|string|url',
          'blueprint.theme.social'    => 'nullable|array',
          'blueprint.theme.social.facebook'  => 'nullable|string|url',
          'blueprint.theme.social.linkedin'  => 'nullable|string|url',
          'blueprint.theme.social.x'        => 'nullable|string|url',
          'blueprint.theme.social.threads'  => 'nullable|string|url',
          'blueprint.theme.social.instagram' => 'nullable|string|url',
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

  // app/Http/Controllers/Instasites/BuildController.php
  public function upsertPost(Request $r){
      $data = $r->validate([
          'hostname'          => 'required|string',
          'locale'            => 'nullable|string',
          'post.title'        => 'required|string',
          'post.slug'         => 'required|string',
          'post.html'         => 'required|string',
          'post.meta_title'   => 'nullable|string',
          'post.meta_description'=>'nullable|string',
          'post.author'       => 'nullable|string',
          'post.category'     => 'nullable|string',
          'post.published_at' => 'nullable|string',
          'post.media_links'  => 'array',
          'blueprint'         => 'array', // optional override (theme/colors/nav)
      ]);

      [$ok,$res] = $this->builder->upsertPost(
          $data['hostname'],
          $data['post'],
          $data['locale'] ?? null,
          $data['blueprint'] ?? null
      );

      return $ok
        ? response()->json(['ok'=>true]+$res)
        : response()->json(['ok'=>false,'error'=>$res['error'] ?? 'failed'], 422);
  }
}

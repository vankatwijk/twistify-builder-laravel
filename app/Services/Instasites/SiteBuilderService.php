<?php

namespace App\Services\Instasites;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class SiteBuilderService
{
  public function __construct(
    private ApacheVirtualHostService $apacheService
  ) {}

  public function build(string $hostname, array $payload): array
  {
    $root    = rtrim(config('instasites.sites_root'), '/')."/{$hostname}";
    $public  = "{$root}/public";
    $theme   = Str::lower(data_get($payload, 'blueprint.theme.name', 'classic'));
    $locales = data_get($payload, 'locales', ['en']);
    $default = data_get($payload, 'blueprint.default_locale', 'en');
    $pages   = data_get($payload, 'pages', []);
    $posts   = data_get($payload, 'posts', []);
    $reset   = (bool) data_get($payload, 'reset', false);

    if ($reset && is_dir($root)) File::deleteDirectory($root);
    File::ensureDirectoryExists($public);

    $assetFlags = $this->copyThemeAssets($theme, "{$public}/assets/{$theme}", data_get($payload, 'blueprint.theme', []));
    $this->renderAll($public, $theme, $locales, $default, $pages, $posts, $payload['blueprint'], $assetFlags);

    // Feeds
    $this->makeSitemap($public, $hostname, $locales, $default, $pages, $posts);
    $this->makeRssFeeds($public, $hostname, $locales, $default, $posts);

    // Manifest saved at site root (like Node)
    $manifest = [
      'theme'           => $theme,
      'locales'         => $locales,
      'default_locale'  => $default,
      'pages'           => $pages,
      'posts'           => $posts,
      'built_at'        => now()->toIso8601String(),
    ];
    File::put("{$root}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

    // Create Apache virtual host
    $vhostCreated = $this->apacheService->createVirtualHost($hostname, $public);
    if (!$vhostCreated) {
      Log::warning("Failed to create Apache virtual host for {$hostname}");
    }

    return [
      'job_id'      => Str::lower(Str::random(8)),
      'pages_count' => count($pages),
      'posts_count' => count($posts),
      'manifest'    => $manifest,
      'apache_vhost_created' => $vhostCreated,
    ];
  }

  public function reset(string $hostname): bool
  {
    $root = rtrim(config('instasites.sites_root'), '/')."/{$hostname}";
    $deleted = false;

    if (is_dir($root)) {
      File::deleteDirectory($root);
      $deleted = true;
      Log::info("Deleted site directory for {$hostname}");
    }

    // Remove Apache virtual host
    $vhostRemoved = $this->apacheService->removeVirtualHost($hostname);
    if (!$vhostRemoved) {
      Log::warning("Failed to remove Apache virtual host for {$hostname}");
    }

    return $deleted;
  }

    private function copyThemeAssets(string $theme, string $dest, array $cfg): array
    {
        $src = base_path("resources/instasites/themes/{$theme}/assets");
        if (is_dir($src)) \Illuminate\Support\Facades\File::copyDirectory($src, $dest);
        \Illuminate\Support\Facades\File::ensureDirectoryExists($dest);

        $vars = ":root{--primary:".($cfg['primaryColor'] ?? '#2563eb').";--accent:".($cfg['accentColor'] ?? '#a855f7').";}";
        file_put_contents("{$dest}/vars.css", $vars);

        return ['hasClassicCss' => is_file("{$src}/classic.css")];
    }

    private function renderAll(
        string $public,
        string $theme,
        array $locales,
        string $default,
        array $pages,
        array $posts,
        array $bp,
        array $assets = []
    ): void {
        $viewBase   = "instasites.themes.{$theme}";
        $layoutView = "{$viewBase}.layouts.base";
        $host = $bp['primary_domain'] ?? 'localhost';
        $norm = fn($s)=>trim((string)$s,'/');

        // Build lookups + fallbacks (same as before)
        $pageByLocSlug=[]; $postByLocSlug=[]; $pageSlugs=[]; $postSlugs=[];
        foreach ($pages as $p){ $loc=$p['locale']??$default; $slug=$norm($p['slug']??''); $pageByLocSlug[$loc][$slug]=$p; $pageSlugs[$slug]=true; }
        foreach ($posts as $p){ $loc=$p['locale']??$default; $slug=$norm($p['slug']??''); $postByLocSlug[$loc][$slug]=$p; $postSlugs[$slug]=true; }
        $pageSlugs=array_keys($pageSlugs); $postSlugs=array_keys($postSlugs);

        foreach ($locales as $loc){
            foreach ($pageSlugs as $slug){
                if(!isset($pageByLocSlug[$loc][$slug]) && isset($pageByLocSlug[$default][$slug])){
                    $f=$pageByLocSlug[$default][$slug]; $f['locale']=$loc; $pageByLocSlug[$loc][$slug]=$f;
                }
            }
            foreach ($postSlugs as $slug){
                if(!isset($postByLocSlug[$loc][$slug]) && isset($postByLocSlug[$default][$slug])){
                    $f=$postByLocSlug[$default][$slug]; $f['locale']=$loc; $postByLocSlug[$loc][$slug]=$f;
                }
            }
        }

        foreach ($locales as $loc){
            $basePath = $loc===$default ? $public : "{$public}/{$loc}";
            if(!is_dir($basePath)) mkdir($basePath,0775,true);

            // PAGES
            foreach (($pageByLocSlug[$loc]??[]) as $slug=>$p){
                $outDir = ($slug===''||$slug==='home') ? $basePath : "{$basePath}/{$slug}";
                if(!is_dir($outDir)) mkdir($outDir,0775,true);

                $canonical = "https://{$host}".($loc===$default?'':"/{$loc}").(($slug===''||$slug==='home')?'/':"/{$slug}/");

                // compute flat vars
                $contentHtml     = (string)($p['html'] ?? '');
                $metaTitle       = $p['meta_title'] ?? $p['title'] ?? ($bp['site_name'] ?? 'Site');
                $metaDescription = $p['meta_description'] ?? '';
                $title           = $p['title'] ?? '';
                // just above the PAGES render
                $navItems = $this->buildNav($bp, ($pageByLocSlug[$loc] ?? []), ($postByLocSlug[$loc] ?? []), $loc, $default);

                // PAGES (inside the foreach)
                $html = view("{$viewBase}.page", [
                    'layout_view'    => $layoutView,
                    'blueprint'      => $bp,
                    'locale'         => $loc,
                    'locales'        => $locales,
                    'defaultLocale'  => $default,
                    'assets'         => $assets,
                    'canonical'      => $canonical,
                    'navItems'       => $navItems,      // ← pass to header partial
                    'title'          => $title,
                    'metaTitle'      => $metaTitle,
                    'metaDescription'=> $metaDescription,
                    'contentHtml'    => $contentHtml,
                ])->render();

                file_put_contents("{$outDir}/index.html", $html);
            }

            // POSTS
            foreach (($postByLocSlug[$loc]??[]) as $slug=>$post){
                $outDir = "{$basePath}/blog/{$slug}";
                if(!is_dir($outDir)) mkdir($outDir,0775,true);

                $canonical = "https://{$host}".($loc===$default?'':"/{$loc}")."/blog/{$slug}/";

                $contentHtml     = (string)($post['html'] ?? '');
                $metaTitle       = $post['meta_title'] ?? $post['title'] ?? ($bp['site_name'] ?? 'Site');
                $metaDescription = $post['meta_description'] ?? '';
                $title           = $post['title'] ?? '';

                $html = view("{$viewBase}.post", [
                    'layout_view'    => $layoutView,
                    'blueprint'      => $bp,
                    'locale'         => $loc,
                    'locales'        => $locales,
                    'defaultLocale'  => $default,
                    'assets'         => $assets,
                    'canonical'      => $canonical,

                    // flat content vars
                    'title'          => $title,
                    'metaTitle'      => $metaTitle,
                    'metaDescription'=> $metaDescription,
                    'contentHtml'    => $contentHtml,
                ])->render();

                file_put_contents("{$outDir}/index.html", $html);
            }
        }
    }


  private function makeSitemap(string $public, string $host, array $locales, string $default, array $pages, array $posts): void
  {
    $urls = [];
    foreach ($locales as $loc) {
      $base = $loc===$default ? '' : "/{$loc}";
      foreach ($pages as $p) if (($p['locale'] ?? $default)===$loc) {
        $slug = $p['slug']==='home' ? '' : '/'.trim($p['slug'],'/');
        $urls[] = "https://{$host}{$base}{$slug}/";
      }
      foreach ($posts as $post) if (($post['locale'] ?? $default)===$loc) {
        $urls[] = "https://{$host}{$base}/blog/".trim($post['slug'],'/')."/";
      }
    }
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $u) $xml .= "<url><loc>{$u}</loc></url>";
    $xml .= '</urlset>';
    file_put_contents("{$public}/sitemap.xml", $xml);
  }

  private function makeRssFeeds(string $public, string $host, array $locales, string $default, array $posts): void
  {
    foreach ($locales as $loc) {
      $base = $loc===$default ? '' : "/{$loc}";
      $locPosts = array_values(array_filter($posts, fn($p)=>($p['locale'] ?? $default)===$loc));
      $items = '';
      foreach ($locPosts as $p) {
        $link = "https://{$host}{$base}/blog/".trim($p['slug'],'/')."/";
        $title = htmlspecialchars($p['meta_title'] ?? $p['title'] ?? 'Post', ENT_XML1);
        $desc  = htmlspecialchars($p['meta_description'] ?? '', ENT_XML1);
        $date  = strtotime($p['published_at'] ?? 'now');
        $items .= "<item><title>{$title}</title><link>{$link}</link><description>{$desc}</description><pubDate>".gmdate(DATE_RSS,$date)."</pubDate></item>";
      }
      $rss = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><rss version=\"2.0\"><channel><title>{$host} {$loc}</title><link>https://{$host}{$base}/</link><description>Blog</description>{$items}</channel></rss>";
      $name = $loc===$default ? 'rss.xml' : "rss.{$loc}.xml";
      file_put_contents("{$public}/{$name}", $rss);
    }
  }

    private function buildNav(array $bp, array $pagesForLoc, array $postsForLoc, string $loc, string $default): array
    {
        $items = [];
        $prefix = $loc === $default ? '' : "/{$loc}";
        $themeNav = $bp['theme']['nav'] ?? [];

        // If explicit items are provided, honor them
        if (!empty($themeNav['items']) && is_array($themeNav['items'])) {
            foreach ($themeNav['items'] as $it) {
                $slug = trim($it['slug'] ?? '', '/');
                $href = $prefix . (($slug === '' || $slug === 'home') ? '/' : "/{$slug}/");
                $items[] = ['title' => $it['title'] ?? ucfirst($slug ?: 'Home'), 'href' => $href];
            }
        } else {
            // Derive from pages (Home + first few)
            $slugs = array_keys($pagesForLoc);
            usort($slugs, fn($a, $b) => ($a === '' || $a === 'home') ? -1 : 1);
            foreach (array_slice($slugs, 0, 6) as $slug) {
                $p = $pagesForLoc[$slug];
                $href = $prefix . (($slug === '' || $slug === 'home') ? '/' : "/{$slug}/");
                $items[] = ['title' => $p['title'] ?? ucfirst($slug ?: 'Home'), 'href' => $href];
            }
        }

        // Optional Blog link
        $includeBlog = $themeNav['includeBlog'] ?? false;
        if (is_string($includeBlog)) {
            $includeBlog = in_array(strtolower($includeBlog), ['1','true','yes','on']);
        }
        if ($includeBlog && !empty($postsForLoc)) {
            $items[] = ['title' => 'Blog', 'href' => $prefix . '/blog/'];
        }

        return $items;
    }

}

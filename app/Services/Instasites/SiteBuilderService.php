<?php

namespace App\Services\Instasites;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class SiteBuilderService
{
  public function upsertPost(string $host, array $post, ?string $locale=null, ?array $bpOverride=null): array
  {
      $root    = rtrim(config('instasites.sites_root'), '/')."/{$host}";
      $public  = "{$root}/public";
      $manifestPath = "{$root}/manifest.json";

      if (!is_dir($public)) {
          // first-touch safety: ensure dirs
          File::ensureDirectoryExists($public);
      }

      // Load existing manifest or create a minimal one
      $manifest = is_file($manifestPath)
          ? json_decode(file_get_contents($manifestPath), true) ?: []
          : [];

      $default   = data_get($manifest, 'default_locale', data_get($bpOverride,'default_locale','en'));
      $locales   = data_get($manifest, 'locales', data_get($bpOverride,'locales', [$default]));
      $blueprint = $bpOverride ?: (data_get($manifest,'blueprint') ?? [
          'site_name' => $host,
          'primary_domain' => $host,
          'default_locale' => $default,
          'theme' => ['name'=> data_get($manifest,'theme','classic')],
      ]);
      $theme     = Str::lower(data_get($blueprint, 'theme.name', data_get($manifest,'theme','classic')));

      if (!in_array($default, $locales, true)) $locales[] = $default;

      // normalize locale
      $loc = $locale ?: $default;

      // ensure theme assets exist (once)
      $this->copyThemeAssets($theme, "{$public}/assets/{$theme}", data_get($blueprint,'theme',[]));

      // merge into manifest posts (idempotent by slug+locale)
      $posts = data_get($manifest,'posts',[]);
      $slug  = trim($post['slug'],'/');
      $post['locale'] = $loc;
      $replaced = false;

      foreach ($posts as $i=>$p) {
          if (($p['locale'] ?? $default)===$loc && trim($p['slug']??'','/')===$slug) {
              $posts[$i] = array_merge($p, $post);
              $replaced = true;
              break;
          }
      }
      if (!$replaced) $posts[] = $post;

      // write the single post page
      $viewBase   = "instasites.themes.{$theme}";
      $layoutView = "{$viewBase}.layouts.base";
      $basePath   = $loc===$default ? $public : "{$public}/{$loc}";
      $outDir     = "{$basePath}/blog/{$slug}";
      File::ensureDirectoryExists($outDir);

      $canonical  = "https://{$host}".($loc===$default?'':"/{$loc}")."/blog/{$slug}/";
      $html = view("{$viewBase}.post", [
          'layout_view'    => $layoutView,
          'blueprint'      => $blueprint,
          'post'           => $post,
          'locale'         => $loc,
          'locales'        => $locales,
          'defaultLocale'  => $default,
          'canonical'      => $canonical,
          // flat fields if your theme expects them
          'title'          => $post['title'] ?? '',
          'metaTitle'      => $post['meta_title'] ?? ($post['title'] ?? ''),
          'metaDescription'=> $post['meta_description'] ?? '',
          'contentHtml'    => (string)($post['html'] ?? ''),
      ])->render();

      file_put_contents("{$outDir}/index.html", $html);

      // re-generate sitemap + locale RSS only (cheap)
      $manifest['theme']          = $theme;
      $manifest['locales']        = array_values(array_unique($locales));
      $manifest['default_locale'] = $default;
      $manifest['blueprint']      = $blueprint;
      $manifest['posts']          = $posts;
      $manifest['built_at']       = now()->toIso8601String();

      File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

      $this->makeSitemap($public, $host, $manifest['locales'], $default, data_get($manifest,'pages',[]), $posts);
      $this->makeRssFeeds($public, $host, $manifest['locales'], $default, $posts);

      return [true, [
          'hostname'=>$host,
          'slug'=>$slug,
          'locale'=>$loc,
      ]];
  }

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

    // Generate favicon
    try {
      $faviconGenerator = new FaviconGeneratorService();
      $faviconGenerator->generate($public, $hostname);
    } catch (\Throwable $e) {
      // Silently fail - favicon is optional
    }

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

    return [
      'job_id'      => Str::lower(Str::random(8)),
      'pages_count' => count($pages),
      'posts_count' => count($posts),
      'manifest'    => $manifest,
    ];
  }

    public function reset(string $hostname): bool
    {
        $root = rtrim(config('instasites.sites_root'), '/')."/{$hostname}";
        if (is_dir($root)) {
        File::deleteDirectory($root);
        return true;
        }
        return false;
    }

    private function copyThemeAssets(string $theme, string $dest, array $cfg): array
    {
        $viewsAssets = resource_path("views/instasites/themes/{$theme}/assets");
        $altAssets   = base_path("resources/instasites/themes/{$theme}/assets");

        $viewsRoot   = resource_path("views/instasites/themes/{$theme}");
        $rootStyle   = "{$viewsRoot}/style.css";
        $rootCssDir  = "{$viewsRoot}/css";
        $rootJsDir   = "{$viewsRoot}/js";
        $rootImgDir  = "{$viewsRoot}/img";
        $rootImages  = "{$viewsRoot}/images";

        \Illuminate\Support\Facades\File::ensureDirectoryExists($dest);

        $src = is_dir($viewsAssets) ? $viewsAssets : (is_dir($altAssets) ? $altAssets : null);
        $copiedAnything = false;

        // 1) Copy /assets if present
        if ($src) {
            try {
                if (\Illuminate\Support\Facades\File::copyDirectory($src, $dest)) {
                    $copiedAnything = true;
                }
            } catch (\Throwable $e) {
                // \Log::warning('copyDirectory failed: '.$e->getMessage());
            }
        }

        // 2) Fallbacks (theme root)
        if (!$copiedAnything) {
            if (is_file($rootStyle)) {
                try { \Illuminate\Support\Facades\File::copy($rootStyle, "{$dest}/style.css"); $copiedAnything = true; } catch (\Throwable $e) {}
            }
            foreach ([$rootCssDir, $rootJsDir, $rootImgDir, $rootImages] as $dir) {
                if (is_dir($dir)) {
                    $name = basename($dir);
                    try { \Illuminate\Support\Facades\File::copyDirectory($dir, "{$dest}/{$name}"); $copiedAnything = true; } catch (\Throwable $e) {}
                }
            }
        }

        // 3) Handle vars.css intelligently
        $destVars = "{$dest}/vars.css";
        $srcVars  = $src ? "{$src}/vars.css" : null;

        $wantPrimary = $cfg['primaryColor'] ?? null;
        $wantAccent  = $cfg['accentColor']  ?? null;
        $force       = (bool)($cfg['forceVarsRegen'] ?? false); // optional flag

        if (is_file($srcVars)) {
            // Theme provides its own vars.css — copy it first (preserve theme defaults)
            try { \Illuminate\Support\Facades\File::copy($srcVars, $destVars); } catch (\Throwable $e) {}

            // If platform config wants to override colors, merge them in-place
            if ($wantPrimary || $wantAccent || $force) {
                try {
                    $css = file_get_contents($destVars) ?: '';
                    if ($wantPrimary) {
                        $css = preg_replace('/(--primary\s*:\s*)([^;]+)(;)/i', '${1}'.$wantPrimary.'$3', $css, 1, $count1);
                        if (!$count1) { $css = rtrim($css)."\n--primary: {$wantPrimary};\n"; }
                    }
                    if ($wantAccent) {
                        $css = preg_replace('/(--accent\s*:\s*)([^;]+)(;)/i', '${1}'.$wantAccent.'$3', $css, 1, $count2);
                        if (!$count2) { $css = rtrim($css)."\n--accent: {$wantAccent};\n"; }
                    }
                    // Ensure :root wrapper exists
                    if (!preg_match('/:root\s*\{[\s\S]*\}/', $css)) {
                        $css = ":root{\n--primary: ".($wantPrimary ?? '#2563eb').";\n--accent: ".($wantAccent ?? '#a855f7').";\n}\n".$css;
                    }
                    file_put_contents($destVars, $css);
                } catch (\Throwable $e) {}
            }
        } else {
            // No theme vars.css — generate one (use platform cfg OR campbell defaults)
            $primary = $wantPrimary ?: ($theme === 'campbell' ? '#ffcc00' : '#2563eb');
            $accent  = $wantAccent  ?: ($theme === 'campbell' ? '#ff6600' : '#a855f7');

            $vars = <<<CSS
                    :root{
                    --primary: {$primary};
                    --accent:  {$accent};
                    /* Campbell/Default dark tokens */
                    --bg:      #0b0b0b;
                    --panel:   #141414;
                    --text:    #f5f5f5;
                    --muted:   #9e9e9e;
                    --outline: rgba(255,255,255,0.08);
                    --ring:    rgba(255,204,0,0.35);
                    }
                    CSS;
            file_put_contents($destVars, $vars);
        }

        return [
            'assetsCopied' => $copiedAnything,
            'source'       => $src ?: 'fallback-views-root',
            'hasStyleCss'  => is_file("{$dest}/style.css"),
            'hasVarsCss'   => is_file($destVars),
            'dest'         => $dest,
        ];
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

                // hero media (first valid URL from media_links)
                $heroMedia = null;
                $ml = $p['media_links'] ?? [];
                if (is_array($ml) && !empty($ml)) {
                    $first = $ml[0] ?? null;
                    if (is_string($first) && preg_match('~^https?://~i', $first)) {
                        $heroMedia = $first;
                    }
                }
                $recentPosts = $this->recentPostsFromMap(($postByLocSlug[$loc] ?? []), $loc, $default);

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

                    // NEW
                    'heroMedia'       => $heroMedia,
                    'recentPosts'    => $recentPosts,   // ← pass to view
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
                $recentPosts = $this->recentPostsFromMap(($postByLocSlug[$loc] ?? []), $loc, $default);
                // hero media
                $heroMedia = null;
                $ml = $post['media_links'] ?? [];
                if (is_array($ml) && !empty($ml)) {
                    $first = $ml[0] ?? null;
                    if (is_string($first) && preg_match('~^https?://~i', $first)) {
                        $heroMedia = $first;
                    }
                }

                $html = view("{$viewBase}.post", [
                    'layout_view'    => $layoutView,
                    'blueprint'      => $bp,
                    'locale'         => $loc,
                    'locales'        => $locales,
                    'defaultLocale'  => $default,
                    'assets'         => $assets,
                    'canonical'      => $canonical,

                    // flat content vars

                    'navItems'      => $navItems,
                    'title'          => $title,
                    'metaTitle'      => $metaTitle,
                    'metaDescription'=> $metaDescription,
                    'contentHtml'    => $contentHtml,

                    // NEW
                    'heroMedia'       => $heroMedia,
                    'recentPosts'    => $recentPosts,   // ← pass to view
                ])->render();

                file_put_contents("{$outDir}/index.html", $html);
            }

            // BLOG INDEX (per locale)
            if (!empty($postByLocSlug[$loc])) {
                $blogDir = "{$basePath}/blog";
                if (!is_dir($blogDir)) mkdir($blogDir, 0775, true);

                $canonicalBlog = "https://{$host}".($loc===$default?'':"/{$loc}")."/blog/";
                $navItems      = $this->buildNav($bp, ($pageByLocSlug[$loc] ?? []), ($postByLocSlug[$loc] ?? []), $loc, $default);
                $postsList     = $this->postsListFromMap(($postByLocSlug[$loc] ?? []), $loc, $default);

                $html = view("{$viewBase}.blog", [
                    'layout_view'     => $layoutView,
                    'blueprint'       => $bp,
                    'locale'          => $loc,
                    'locales'         => $locales,
                    'defaultLocale'   => $default,
                    'assets'          => $assets,
                    'canonical'       => $canonicalBlog,

                    'navItems'        => $navItems,
                    'title'           => 'Blog',
                    'metaTitle'       => 'Blog',
                    'metaDescription' => $bp['site_name'] ? ($bp['site_name'].' blog') : 'Blog',
                    'postsList'       => $postsList,
                    // Optional hero image for the blog index: leave null
                    'heroMedia'       => null,
                ])->render();

                file_put_contents("{$blogDir}/index.html", $html);
            }
        }
    }


  private function makeSitemap(string $public, string $host, array $locales, string $default, array $pages, array $posts): void
  {
    $urls = [];
    foreach ($locales as $loc) {
        $base = $loc===$default ? '' : "/{$loc}";
        $hasLocPosts = false;

        foreach ($pages as $p) if (($p['locale'] ?? $default)===$loc) {
            $slug = $p['slug']==='home' ? '' : '/'.trim($p['slug'],'/');
            $urls[] = "https://{$host}{$base}{$slug}/";
        }
        foreach ($posts as $post) if (($post['locale'] ?? $default)===$loc) {
            $urls[] = "https://{$host}{$base}/blog/".trim($post['slug'],'/')."/";
            $hasLocPosts = true;
        }

        if ($hasLocPosts) {
            $urls[] = "https://{$host}{$base}/blog/"; // blog index
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

    private function recentPostsFromMap(array $postsForLoc, string $loc, string $default, int $limit = 5): array
    {
        // $postsForLoc is the map you already build: [slug => postArray]
        $prefix = $loc === $default ? '' : "/{$loc}";

        $list = [];
        foreach ($postsForLoc as $slug => $p) {
            $title = $p['meta_title'] ?? $p['title'] ?? null;
            if (!$title) continue;

            $href = "{$prefix}/blog/".trim($slug, '/')."/";
            $ts   = strtotime($p['published_at'] ?? '') ?: 0;

            $list[] = [
                'title' => $title,
                'href'  => $href,
                'desc'  => $p['meta_description'] ?? '',
                'ts'    => $ts,
            ];
        }

        usort($list, fn($a,$b) => $b['ts'] <=> $a['ts']); // newest first
        return array_slice($list, 0, $limit);
    }

    private function postsListFromMap(array $postsForLoc, string $loc, string $default): array
    {
        $prefix = $loc === $default ? '' : "/{$loc}";
        $out = [];
        foreach ($postsForLoc as $slug => $p) {
            $title = $p['meta_title'] ?? $p['title'] ?? null;
            if (!$title) continue;
            $out[] = [
                'title' => $title,
                'href'  => "{$prefix}/blog/".trim($slug, '/')."/",
                'desc'  => $p['meta_description'] ?? '',
                'date'  => $p['published_at'] ?? null,
                'ts'    => strtotime($p['published_at'] ?? '') ?: 0,
            ];
        }
        usort($out, fn($a,$b) => $b['ts'] <=> $a['ts']); // newest first
        return $out;
    }

}

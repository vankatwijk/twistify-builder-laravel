@php $themeName = strtolower($blueprint['theme']['name'] ?? 'general'); @endphp
@php
  $ctaText = $blueprint['theme']['cta']['text'] ?? 'New Project';
  $ctaHref = $blueprint['theme']['cta']['href'] ?? '#';

  $title = $blueprint['theme']['hero']['title'] ?? 'Chat-style, clean, and fast.';
  $sub   = $blueprint['theme']['hero']['subtitle'] ?? 'A minimal, dark UI with emerald accents—optimized for readability and speed.';
  $cta1  = $blueprint['theme']['hero']['cta_primary'] ?? ['text' => $ctaText, 'href' => $ctaHref];
  $cta2  = $blueprint['theme']['hero']['cta_secondary'] ?? ['text' => 'Docs', 'href' => '#features'];
@endphp
{{-- Bootstrap, vendor CSS, etc. should come first --}}
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="icon" href="/assets/favicon.png" type="image/png">
<link rel="stylesheet" href="/assets/{{ $themeName }}/style.css">
<link rel="stylesheet" href="/assets/{{ $themeName }}/vars.css">

<main class="gen-main container">
  @includeIf("instasites.themes.$themeName.partials.sidebar")   {{-- <— Add this --}}

  <div class="gen-card">
    <div class="chat-pane-head">
      <div class="muted">{{ $blueprint['site_name'] ?? 'Site' }}</div>
      {{-- Right-side tools could go here --}}
    </div>

    <div class="chat-scroll">
      {{-- Example messages mapped from your CMS --}}
      <div class="bubble user">
        <span class="avatar" aria-hidden="true"></span>
        <div class="content">
          <p>Show me the latest SEO checklist.</p>
        </div>
      </div>

      <div class="bubble assistant">
        <span class="avatar" aria-hidden="true"></span>
        <div class="content">
          <p>Here’s a concise checklist to get you started:</p>
          <ul>
            <li>Fix crawl errors</li>
            <li>Optimize titles & meta</li>
            <li>Improve Core Web Vitals</li>
          </ul>
        </div>
      </div>

      <div class="gen-hero-art">
        @if(!empty($heroMedia))
          <img src="{{ $heroMedia }}" alt="{{ $blueprint['site_name'] ?? 'Hero' }}" onerror="this.style.display='none'">
        @elseif(!empty($assets['assetsCopied'])) 
          {{-- Fallback (optional) --}}
          <img src="/assets/campbell/placeholder-headshot.jpg" alt="Placeholder" onerror="this.style.display='none'">
        @endif
      </div>

      {{-- Render your post/page blocks as bubbles if you want --}}
      @yield('content')
    </div>
  </div>


  @includeIf("instasites.themes.$themeName.partials.features")
</main>

@includeIf("instasites.themes.$themeName.partials.footer")
@includeIf("instasites.themes.$themeName.partials.lang-switcher")

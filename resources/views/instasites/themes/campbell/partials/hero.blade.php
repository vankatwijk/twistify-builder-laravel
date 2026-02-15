@php
  $bp       = $blueprint ?? [];
  $siteName = $bp['site_name'] ?? 'Site';
  // Make a Craig-style, punchy default. Can be overridden later via blueprint['hero'].
  $hero = $bp['hero'] ?? [];
  $title    = $hero['title']    ?? "SEO Training, Services & Real Results";
  $subtitle = $hero['subtitle'] ?? "Actionable strategies, audits, and hands-on consulting.";
  $primary  = $hero['primary']  ?? ['label'=>'Work With Me', 'href'=>'/services'];
  $secondary= $hero['secondary']?? ['label'=>'Read the Blog', 'href'=>'/blog'];
  $badges   = $hero['badges']   ?? ['15+ Years Experience','No-fluff Strategies','Case Studies & SOPs'];
@endphp

<section class="gen-hero">
  <div class="container gen-hero-inner">
    <div class="gen-hero-copy">
      <h1 class="gen-hero-title">
        {{ $metaTitle ?? ($title ?? ($blueprint['site_name'] ?? '')) }}
      </h1>
      @if(!empty($metaDescription))
        <p class="gen-hero-sub">{{ $metaDescription }}</p>
      @endif
    </div>

    <div class="gen-hero-art">
      @if(!empty($heroMedia))
        <img src="{{ $heroMedia }}" alt="{{ $blueprint['site_name'] ?? 'Hero' }}" onerror="this.style.display='none'">
      @elseif(!empty($assets['assetsCopied'])) 
        {{-- Fallback (optional) --}}
        <img src="/assets/campbell/placeholder-headshot.jpg" alt="Placeholder" onerror="this.style.display='none'">
      @endif
    </div>
  </div>
</section>

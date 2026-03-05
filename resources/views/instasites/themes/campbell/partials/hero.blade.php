@php
  $bp       = $blueprint ?? [];
  $siteName = $bp['site_name'] ?? 'Site';
  $hero = $bp['theme']['hero'] ?? [];
  $title = $hero['headline'] ?? ($metaTitle ?? $siteName);
  $subtitle = $hero['subheadline'] ?? ($metaDescription ?? null);
  $primaryText = $hero['ctaText'] ?? null;
  $primaryHref = $hero['ctaHref'] ?? '#';
  $secondaryText = $hero['secondaryCtaText'] ?? null;
  $secondaryHref = $hero['secondaryCtaHref'] ?? '#';
  $heroImage = $hero['imageUrl'] ?? ($heroMedia ?? null);
@endphp

<section class="gen-hero">
  <div class="container gen-hero-inner">
    <div class="gen-hero-copy">
      <h1 class="gen-hero-title">
        {{ $title }}
      </h1>
      @if(!empty($subtitle))
        <p class="gen-hero-sub">{{ $subtitle }}</p>
      @endif
      @if(!empty($primaryText) || !empty($secondaryText))
        <div class="gen-cta-row">
          @if(!empty($primaryText))
            <a class="gen-hero-cta" href="{{ $primaryHref }}">{{ $primaryText }}</a>
          @endif
          @if(!empty($secondaryText))
            <a class="gen-hero-cta ghost" href="{{ $secondaryHref }}">{{ $secondaryText }}</a>
          @endif
        </div>
      @endif
    </div>

    <div class="gen-hero-art">
      @if(!empty($heroImage))
        <img src="{{ $heroImage }}" alt="{{ $blueprint['site_name'] ?? 'Hero' }}" onerror="this.style.display='none'">
      @elseif(!empty($assets['assetsCopied'])) 
        {{-- Fallback (optional) --}}
        <img src="/assets/campbell/placeholder-headshot.jpg" alt="Placeholder" onerror="this.style.display='none'">
      @endif
    </div>
  </div>
</section>

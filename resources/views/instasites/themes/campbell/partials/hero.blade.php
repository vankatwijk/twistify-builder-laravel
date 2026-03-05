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
  @if(!empty($heroImage) || !empty($assets['assetsCopied']))
    @php
      $bannerImage = $heroImage ?: '/assets/campbell/placeholder-headshot.jpg';
    @endphp
    <div class="gen-hero-banner-wrap">
      <div class="gen-hero-banner">
        <img src="{{ $bannerImage }}" alt="{{ $blueprint['site_name'] ?? 'Hero' }}" onerror="this.style.display='none'">
        <div class="gen-hero-overlay container">
          <div class="gen-hero-copy">
            <h1 class="gen-hero-title">{{ $title }}</h1>
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
        </div>
      </div>
    </div>
  @else
    <div class="container gen-hero-inner">
      <div class="gen-hero-copy">
        <h1 class="gen-hero-title">{{ $title }}</h1>
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
    </div>
  @endif
</section>

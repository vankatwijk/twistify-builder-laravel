@php
  $hero = $blueprint['theme']['hero'] ?? [];
  $headline = $hero['headline'] ?? ($metaTitle ?? ($blueprint['site_name'] ?? 'Site'));
  $subheadline = $hero['subheadline'] ?? ($metaDescription ?? null);
  $primaryText = $hero['ctaText'] ?? null;
  $primaryHref = $hero['ctaHref'] ?? '#';
  $secondaryText = $hero['secondaryCtaText'] ?? null;
  $secondaryHref = $hero['secondaryCtaHref'] ?? '#';
  $heroImage = $hero['imageUrl'] ?? ($heroMedia ?? null);
@endphp

@if(!empty($headline) || !empty($heroImage))
  <section class="classic-hero">
    @if(!empty($heroImage))
      <div class="classic-hero-banner-wrap">
        <div class="classic-hero-banner">
          <img src="{{ $heroImage }}" alt="{{ $blueprint['site_name'] ?? 'Hero' }}" onerror="this.style.display='none'">
          <div class="classic-hero-overlay container">
            <div class="classic-hero-content">
              @if(!empty($headline))
                <h1>{{ $headline }}</h1>
              @endif
              @if(!empty($subheadline))
                <p class="classic-hero-subtitle">{{ $subheadline }}</p>
              @endif
              @if(!empty($primaryText) || !empty($secondaryText))
                <div class="classic-hero-actions">
                  @if(!empty($primaryText))
                    <a class="classic-hero-btn" href="{{ $primaryHref }}">{{ $primaryText }}</a>
                  @endif
                  @if(!empty($secondaryText))
                    <a class="classic-hero-btn ghost" href="{{ $secondaryHref }}">{{ $secondaryText }}</a>
                  @endif
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="container classic-hero-inner">
        <div class="classic-hero-content">
          @if(!empty($headline))
            <h1>{{ $headline }}</h1>
          @endif
          @if(!empty($subheadline))
            <p class="classic-hero-subtitle">{{ $subheadline }}</p>
          @endif
          @if(!empty($primaryText) || !empty($secondaryText))
            <div class="classic-hero-actions">
              @if(!empty($primaryText))
                <a class="classic-hero-btn" href="{{ $primaryHref }}">{{ $primaryText }}</a>
              @endif
              @if(!empty($secondaryText))
                <a class="classic-hero-btn ghost" href="{{ $secondaryHref }}">{{ $secondaryText }}</a>
              @endif
            </div>
          @endif
        </div>
      </div>
    @endif
  </section>
@endif

@php
  $hero = $blueprint['theme']['hero'] ?? [];
  $headline = $hero['headline'] ?? ($metaTitle ?? ($blueprint['site_name'] ?? 'Welcome'));
  $subheadline = $hero['subheadline'] ?? ($metaDescription ?? null);
  $heroImage = $hero['imageUrl'] ?? ($heroMedia ?? null);
  $overlay = $hero['overlayOpacity'] ?? 0.35;
@endphp

<section class="galaxy-hero">
  <div class="container galaxy-hero-inner">
    <div class="galaxy-hero-copy">
      <p class="galaxy-kicker">Odds · Tips · Insights</p>
      <h1>{{ $headline }}</h1>
      @if(!empty($subheadline))
        <p class="galaxy-sub">{{ $subheadline }}</p>
      @endif
      <div class="galaxy-actions">
        @if(!empty($hero['ctaText']))
          <a class="galaxy-btn primary" href="{{ $hero['ctaHref'] ?? '#' }}">{{ $hero['ctaText'] }}</a>
        @endif
        @if(!empty($hero['secondaryCtaText']))
          <a class="galaxy-btn secondary" href="{{ $hero['secondaryCtaHref'] ?? '#' }}">{{ $hero['secondaryCtaText'] }}</a>
        @endif
      </div>
    </div>

    @if(!empty($heroImage))
      <div class="galaxy-hero-media" style="--hero-overlay: {{ $overlay }};">
        <img src="{{ $heroImage }}" alt="{{ $blueprint['site_name'] ?? 'Hero' }}" loading="lazy">
      </div>
    @endif
  </div>
</section>

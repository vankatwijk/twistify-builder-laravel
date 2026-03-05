@php
  $hero = $blueprint['theme']['hero'] ?? null;
@endphp
@if($hero)
<section class="gen-hero">
  <div class="container gen-hero-inner">
    <div class="gen-hero-copy">
      <h1 class="gen-hero-title">{{ $hero['headline'] ?? ($blueprint['site_name'] ?? 'Welcome') }}</h1>
      @if(!empty($hero['subheadline']))
        <p class="gen-hero-sub">{{ $hero['subheadline'] }}</p>
      @endif
      @if(!empty($hero['ctaText']) || !empty($hero['secondaryCtaText']))
        <div class="gen-cta-row">
          @if(!empty($hero['ctaText']))
            <a class="gen-hero-cta" href="{{ $hero['ctaHref'] ?? '#' }}">{{ $hero['ctaText'] }}</a>
          @endif
          @if(!empty($hero['secondaryCtaText']))
            <a class="gen-hero-cta ghost" href="{{ $hero['secondaryCtaHref'] ?? '#' }}">{{ $hero['secondaryCtaText'] }}</a>
          @endif
        </div>
      @endif
      @if(!empty($hero['badges']) && is_array($hero['badges']))
        <div class="gen-badges">
          @foreach($hero['badges'] as $b)
            <div class="gen-badge">{{ $b }}</div>
          @endforeach
        </div>
      @endif
    </div>
    @if(!empty($hero['imageUrl']))
      <div class="gen-hero-art">
        <img src="{{ $hero['imageUrl'] }}" alt="" loading="lazy">
      </div>
    @endif
  </div>
</section>
@endif

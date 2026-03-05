@php
  $hero = $blueprint['theme']['hero'] ?? [];
  $navCta = $blueprint['theme']['nav']['cta'] ?? [];
  $ctaEnabled = !empty($navCta['enabled']);
  $ctaText = $navCta['text'] ?? 'Get Started';
  $ctaHref = $navCta['href'] ?? '#';
  $ctaTarget = !empty($navCta['newTab']) ? '_blank' : '_self';
  $ctaRel = !empty($navCta['newTab']) ? 'noopener noreferrer' : null;
@endphp

<header class="cyb-header">
  <div class="container cyb-nav">
    <a class="cyb-logo" href="{{ $locale === $defaultLocale ? '/' : "/$locale/" }}">
      <span class="cyb-logo-mark">◉</span>
      <span>{{ $blueprint['theme']['logoText'] ?? $blueprint['site_name'] ?? 'Site' }}</span>
    </a>

    @if(!empty($navItems))
      <nav class="cyb-menu">
        @foreach($navItems as $item)
          <a class="cyb-link" href="{{ $item['href'] }}">{{ $item['title'] }}</a>
        @endforeach
      </nav>
    @endif

    @if($ctaEnabled)
      <a class="cyb-cta" href="{{ $ctaHref }}" target="{{ $ctaTarget }}" @if($ctaRel) rel="{{ $ctaRel }}" @endif>{{ $ctaText }}</a>
    @endif
  </div>

  {{-- subtle hero bar --}}
  <div class="cyb-hero">
    <div class="container">
      <h1 class="cyb-hero-title">{{ $hero['headline'] ?? ($blueprint['site_name'] ?? 'Welcome') }}</h1>
      <p class="cyb-hero-sub">{{ $hero['subheadline'] ?? 'fast • static • multilingual' }}</p>
    </div>
  </div>
</header>

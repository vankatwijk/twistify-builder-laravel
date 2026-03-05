@php
  $homeHref = $locale === $defaultLocale ? '/' : "/$locale/";
  $logoText = $blueprint['theme']['logoText'] ?? $blueprint['site_name'] ?? 'Site';
  $logoUrl = $blueprint['theme']['logoUrl'] ?? null;
  $navCta = $blueprint['theme']['nav']['cta'] ?? [];
  $ctaEnabled = !empty($navCta['enabled']);
  $ctaText = $navCta['text'] ?? 'Join Now';
  $ctaHref = $navCta['href'] ?? '#';
  $ctaTarget = !empty($navCta['newTab']) ? '_blank' : '_self';
  $ctaRel = !empty($navCta['newTab']) ? 'noopener noreferrer' : null;
@endphp

<header class="galaxy-header">
  <div class="container galaxy-nav">
    <a class="galaxy-logo" href="{{ $homeHref }}">
      @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $logoText }}">
      @else
        <span class="galaxy-logo-dot"></span>
        <span>{{ $logoText }}</span>
      @endif
    </a>

    @if(!empty($navItems))
      <nav class="galaxy-menu">
        @foreach($navItems as $item)
          <a class="galaxy-link" href="{{ $item['href'] }}">{{ $item['title'] }}</a>
        @endforeach
      </nav>
    @endif

    @if(!empty($navItems) || $ctaEnabled)
      <details class="galaxy-mobile-nav">
        <summary class="galaxy-mobile-toggle" aria-label="Open menu">
          <span class="galaxy-burger" aria-hidden="true"></span>
          <span class="galaxy-burger" aria-hidden="true"></span>
          <span class="galaxy-burger" aria-hidden="true"></span>
        </summary>
        <nav class="galaxy-mobile-panel" aria-label="Mobile navigation">
          @foreach(($navItems ?? []) as $item)
            <a class="galaxy-link" href="{{ $item['href'] }}">{{ $item['title'] }}</a>
          @endforeach
          @if($ctaEnabled)
            <a class="galaxy-cta" href="{{ $ctaHref }}" target="{{ $ctaTarget }}" @if($ctaRel) rel="{{ $ctaRel }}" @endif>{{ $ctaText }}</a>
          @endif
        </nav>
      </details>
    @endif

    @if($ctaEnabled)
      <a class="galaxy-cta" href="{{ $ctaHref }}" target="{{ $ctaTarget }}" @if($ctaRel) rel="{{ $ctaRel }}" @endif>{{ $ctaText }}</a>
    @endif
  </div>
</header>

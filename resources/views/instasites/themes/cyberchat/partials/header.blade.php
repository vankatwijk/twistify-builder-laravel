@php
  $site    = $blueprint['site_name'] ?? 'Site';
  $navCta = $blueprint['theme']['nav']['cta'] ?? [];
  $ctaEnabled = !empty($navCta['enabled']);
  $ctaText = $navCta['text'] ?? 'New Project';
  $ctaHref = $navCta['href'] ?? '#';
  $ctaTarget = !empty($navCta['newTab']) ? '_blank' : '_self';
  $ctaRel = !empty($navCta['newTab']) ? 'noopener noreferrer' : null;
  $logoUrl = $blueprint['theme']['logoUrl'] ?? null;
@endphp

<header class="gen-header">
  <div class="container gen-nav">
    <a class="gen-logo" href="/">
      @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $site }}" class="gen-logo-img" style="max-height: 40px; width: auto;">
      @else
        <span class="gen-logo-dot"></span>
        <span class="gen-logo-text">{{ $site }}</span>
      @endif
    </a>

    {{-- Desktop --}}
    <nav class="nav-desktop" aria-label="Primary">
      @foreach(($navItems ?? []) as $item)
        <a class="gen-link" href="{{ $item['href'] }}">{{ $item['title'] }}</a>
      @endforeach
      @if($ctaEnabled)
        <a class="gen-cta" href="{{ $ctaHref }}" target="{{ $ctaTarget }}" @if($ctaRel) rel="{{ $ctaRel }}" @endif>{{ $ctaText }}</a>
      @endif
    </nav>

    {{-- Mobile --}}
    <details class="nav-mobile">
      <summary class="nav-mobile-toggle">
        <span class="burger-stack" aria-hidden="true">
          <span class="burger"></span>
          <span class="burger"></span>
          <span class="burger"></span>
        </span>
        <span class="nav-mobile-label">Menu</span>
      </summary>
      <nav class="nav-mobile-panel" aria-label="Primary mobile">
        @foreach(($navItems ?? []) as $item)
          <a class="gen-link" href="{{ $item['href'] }}">{{ $item['title'] }}</a>
        @endforeach
        @if($ctaEnabled)
          <a class="gen-cta" href="{{ $ctaHref }}" target="{{ $ctaTarget }}" @if($ctaRel) rel="{{ $ctaRel }}" @endif>{{ $ctaText }}</a>
        @endif
      </nav>
    </details>
  </div>
</header>

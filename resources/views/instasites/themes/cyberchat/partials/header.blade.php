@php
  $site    = $blueprint['site_name'] ?? 'Site';
  $ctaText = $blueprint['theme']['cta']['text'] ?? 'New Project';
  $ctaHref = $blueprint['theme']['cta']['href'] ?? '#';
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
      <a class="gen-cta" href="{{ $ctaHref }}">{{ $ctaText }}</a>
    </nav>

    {{-- Mobile --}}
    <details class="nav-mobile">
      <summary class="nav-mobile-toggle">
        <span class="burger"></span><span class="burger"></span><span class="burger"></span>
        <span class="nav-mobile-label">Menu</span>
      </summary>
      <nav class="nav-mobile-panel" aria-label="Primary mobile">
        @foreach(($navItems ?? []) as $item)
          <a class="gen-link" href="{{ $item['href'] }}">{{ $item['title'] }}</a>
        @endforeach
        <a class="gen-cta" href="{{ $ctaHref }}">{{ $ctaText }}</a>
      </nav>
    </details>
  </div>
</header>

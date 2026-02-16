@php
  $homeHref = $locale === $defaultLocale ? '/' : "/$locale/";
  $ctaText  = $blueprint['theme']['cta']['text'] ?? 'Sign Up';
  $ctaHref  = $blueprint['theme']['cta']['href'] ?? '#';
  $logoUrl  = $blueprint['theme']['logoUrl'] ?? null;
  $logoText = $blueprint['theme']['logoText'] ?? $blueprint['site_name'] ?? 'Site';
@endphp

<header class="gen-header">
  <div class="container gen-nav">
    <a class="gen-logo" href="{{ $homeHref }}">
      @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $logoText }}" class="gen-logo-img" style="max-height: 40px; width: auto;">
      @else
        <span class="gen-logo-dot"></span>
        <span>{{ $logoText }}</span>
      @endif
    </a>

    @if(!empty($navItems))
      <details class="gen-menu-wrap" open>
        <summary class="gen-menu-toggle" aria-label="Toggle navigation">
          <span class="burger-stack" aria-hidden="true">
            <span class="burger"></span>
            <span class="burger"></span>
            <span class="burger"></span>
          </span>
          <span class="gen-menu-label">Menu</span>
        </summary>
        <nav class="gen-menu">
          @foreach($navItems as $item)
            <a class="gen-link" href="{{ $item['href'] }}">{{ $item['title'] }}</a>
          @endforeach
        </nav>
      </details>
    @endif

    {{-- <a class="gen-cta" href="{{ $ctaHref }}">{{ $ctaText }}</a> --}}
  </div>
</header>



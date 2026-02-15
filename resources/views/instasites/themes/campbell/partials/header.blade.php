@php
  $homeHref = $locale === $defaultLocale ? '/' : "/$locale/";
  $ctaText  = $blueprint['theme']['cta']['text'] ?? 'Sign Up';
  $ctaHref  = $blueprint['theme']['cta']['href'] ?? '#';
@endphp

<header class="gen-header">
  <div class="container gen-nav">
    <a class="gen-logo" href="{{ $homeHref }}">
      <span class="gen-logo-dot"></span>
      <span>{{ $blueprint['theme']['logoText'] ?? $blueprint['site_name'] ?? 'Site' }}</span>
    </a>

    @if(!empty($navItems))
      <nav class="gen-menu">
        @foreach($navItems as $item)
          <a class="gen-link" href="{{ $item['href'] }}">{{ $item['title'] }}</a>
        @endforeach
      </nav>
    @endif

    {{-- <a class="gen-cta" href="{{ $ctaHref }}">{{ $ctaText }}</a> --}}
  </div>
</header>



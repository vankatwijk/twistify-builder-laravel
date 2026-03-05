@php
  $homeHref = $locale === $defaultLocale ? '/' : "/$locale/";
  $navCta = $blueprint['theme']['nav']['cta'] ?? [];
  $ctaEnabled = !empty($navCta['enabled']);
  $ctaText = $navCta['text'] ?? 'Sign Up';
  $ctaHref = $navCta['href'] ?? '#';
  $ctaTarget = !empty($navCta['newTab']) ? '_blank' : '_self';
  $ctaRel = !empty($navCta['newTab']) ? 'noopener noreferrer' : null;
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

    @if($ctaEnabled)
      <a class="gen-cta" href="{{ $ctaHref }}" target="{{ $ctaTarget }}" @if($ctaRel) rel="{{ $ctaRel }}" @endif>{{ $ctaText }}</a>
    @endif
  </div>
</header>

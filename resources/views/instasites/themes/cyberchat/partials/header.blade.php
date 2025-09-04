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
  </div>

  {{-- subtle hero bar --}}
  <div class="cyb-hero">
    <div class="container">
      <h1 class="cyb-hero-title">{{ $blueprint['site_name'] ?? 'Welcome' }}</h1>
      <p class="cyb-hero-sub">fast • static • multilingual</p>
    </div>
  </div>
</header>

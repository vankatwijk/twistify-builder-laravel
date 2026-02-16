<nav class="container d-flex align-items-center justify-content-between py-3">
  @php
    $logoUrl = $blueprint['theme']['logoUrl'] ?? null;
    $logoText = $blueprint['theme']['logoText'] ?? $blueprint['site_name'] ?? 'Site';
  @endphp
  <a href="{{ $locale === $defaultLocale ? '/' : "/$locale/" }}" class="navbar-brand text-decoration-none">
    @if($logoUrl)
      <img src="{{ $logoUrl }}" alt="{{ $logoText }}" style="max-height: 40px; width: auto;">
    @else
      {{ $logoText }}
    @endif
  </a>

  @if(!empty($navItems))
    <ul class="nav">
      @foreach($navItems as $item)
        <li class="nav-item">
          <a class="nav-link" href="{{ $item['href'] }}">{{ $item['title'] }}</a>
        </li>
      @endforeach
    </ul>
  @endif
</nav>
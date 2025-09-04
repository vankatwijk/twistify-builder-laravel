<nav class="container d-flex align-items-center justify-content-between py-3">
  <a href="{{ $locale === $defaultLocale ? '/' : "/$locale/" }}" class="navbar-brand text-decoration-none">
    {{ $blueprint['theme']['logoText'] ?? $blueprint['site_name'] ?? 'Site' }}
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
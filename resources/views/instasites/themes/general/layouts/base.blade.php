<!doctype html>
<html lang="{{ $locale }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $metaTitle ?? ($blueprint['site_name'] ?? 'Site') }}</title>
  <link rel="icon" href="/assets/favicon.png" type="image/png">
  <meta name="description" content="{{ $metaDescription ?? '' }}">
  @isset($canonical)<link rel="canonical" href="{{ $canonical }}">@endisset
  @php $themeName = strtolower($blueprint['theme']['name'] ?? 'general'); @endphp
  <link rel="stylesheet" href="/assets/{{ $themeName }}/style.css">
  <link rel="stylesheet" href="/assets/{{ $themeName }}/vars.css">
</head>
<body class="gen-body">
  @includeIf("instasites.themes.$themeName.partials.header", ['navItems' => $navItems ?? []])

  {{-- Promo / Hero (reads optional config) --}}
  @includeIf("instasites.themes.$themeName.partials.hero")

  <main class="gen-main container">
    <div class="gen-card">
      @yield('content')
    </div>

    {{-- Optional features (badges) --}}
    @includeIf("instasites.themes.$themeName.partials.features")
  </main>

  @includeIf("instasites.themes.$themeName.partials.footer")
  @includeIf("instasites.themes.$themeName.partials.lang-switcher")
</body>
</html>

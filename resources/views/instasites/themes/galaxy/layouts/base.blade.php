<!doctype html>
<html lang="{{ $locale }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $metaTitle ?? ($blueprint['site_name'] ?? 'Site') }}</title>
  <meta name="description" content="{{ $metaDescription ?? '' }}">
  @isset($canonical)<link rel="canonical" href="{{ $canonical }}">@endisset

  @php $themeName = strtolower($blueprint['theme']['name'] ?? 'galaxy'); @endphp
  <link rel="icon" href="/assets/favicon.png" type="image/png">
  <link rel="stylesheet" href="/assets/{{ $themeName }}/vars.css">
  <link rel="stylesheet" href="/assets/{{ $themeName }}/style.css">
</head>
<body class="galaxy-body">
  @includeIf("instasites.themes.$themeName.partials.header")
  @includeIf("instasites.themes.$themeName.partials.hero")
  @includeIf("instasites.themes.$themeName.partials.trust")

  <main class="galaxy-main container">
    <section class="galaxy-panel">
      @yield('content')
    </section>
  </main>

  @includeIf("instasites.themes.$themeName.partials.footer")
  @includeIf("instasites.themes.$themeName.partials.popup")
  @includeIf("instasites.themes.$themeName.partials.lang-switcher")
</body>
</html>

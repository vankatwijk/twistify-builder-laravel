@php $themeName = strtolower($blueprint['theme']['name'] ?? 'general'); @endphp
<!doctype html>
<html lang="{{ $locale }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $metaTitle ?? ($blueprint['site_name'] ?? 'Site') }}</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="icon" href="/assets/favicon.png" type="image/png">
  <link rel="stylesheet" href="/assets/{{ $themeName }}/style.css">
  <link rel="stylesheet" href="/assets/{{ $themeName }}/vars.css">
  <meta name="description" content="{{ $metaDescription ?? '' }}">
  @isset($canonical)<link rel="canonical" href="{{ $canonical }}">@endisset
</head>
<body>
  @includeIf("instasites.themes.$themeName.partials.header")
  @includeIf("instasites.themes.$themeName.partials.hero")
  <main class="gen-main container">
    <div class="gen-card">@yield('content')</div>
    @includeIf("instasites.themes.$themeName.partials.features")
  </main>
  @includeIf("instasites.themes.$themeName.partials.footer")
  @includeIf("instasites.themes.$themeName.partials.lang-switcher")
</body>
</html>

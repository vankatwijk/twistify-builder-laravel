<!doctype html>
<html lang="{{ $locale }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $metaTitle ?? ($blueprint['site_name'] ?? 'Site') }}</title>

  @php $themeName = strtolower($blueprint['theme']['name'] ?? 'classic'); @endphp
  <link rel="stylesheet" href="/assets/{{ $themeName }}/style.css">
  <link rel="stylesheet" href="/assets/{{ $themeName }}/vars.css">
  @if(!empty($assets['hasClassicCss']))
    <link rel="stylesheet" href="/assets/{{ $themeName }}/classic.css">
  @endif

  <meta name="description" content="{{ $metaDescription ?? '' }}">
  @isset($canonical)<link rel="canonical" href="{{ $canonical }}">@endisset
</head>
<body>
  @includeIf("instasites.themes.$themeName.partials.header")
  @includeIf("instasites.themes.$themeName.partials.lang-switcher")

  <main class="container my-5">@yield('content')</main>
  @includeIf("instasites.themes.$themeName.partials.footer")
</body>
</html>

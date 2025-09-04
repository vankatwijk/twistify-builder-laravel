<!doctype html>
<html lang="{{ $locale }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>{{ $metaTitle ?? ($blueprint['site_name'] ?? 'Site') }}</title>
  <meta name="description" content="{{ $metaDescription ?? '' }}">
  @isset($canonical)<link rel="canonical" href="{{ $canonical }}">@endisset

  @php $themeName = strtolower($blueprint['theme']['name'] ?? 'cyberchat'); @endphp
  <link rel="stylesheet" href="/assets/{{ $themeName }}/style.css">
  <link rel="stylesheet" href="/assets/{{ $themeName }}/vars.css">
  @if(!empty($assets['hasClassicCss']))
    {{-- optional extra overrides if present --}}
    <link rel="stylesheet" href="/assets/{{ $themeName }}/classic.css">
  @endif
</head>
<body class="cyb-body">
  <div class="cyb-grid">
    @includeIf("instasites.themes.$themeName.partials.header", ['navItems' => $navItems ?? []])

    <main class="cyb-main container">
      <div class="cyb-card">
        @yield('content')
      </div>
    </main>

    @includeIf("instasites.themes.$themeName.partials.footer")
  </div>

  @includeIf("instasites.themes.$themeName.partials.lang-switcher")
</body>
</html>

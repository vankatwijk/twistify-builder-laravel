@php $themeName = strtolower($blueprint['theme']['name'] ?? 'cyberchat'); @endphp
<!doctype html>
<html lang="{{ $locale }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $metaTitle ?? ($blueprint['site_name'] ?? 'Site') }}</title>
  <meta name="description" content="{{ $metaDescription ?? '' }}">
  @isset($canonical)<link rel="canonical" href="{{ $canonical }}">@endisset

  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="icon" href="/assets/favicon.png" type="image/png">
  <link rel="stylesheet" href="/assets/{{ $themeName }}/vars.css">
  <link rel="stylesheet" href="/assets/{{ $themeName }}/style.css">
</head>
<body class="cyb-body">
  <main class="cyb-shell">
    @includeIf("instasites.themes.$themeName.partials.sidebar")

    <section class="cyb-main" aria-label="Conversation">
      <header class="cyb-topbar">
        <div class="cyb-topbar-title">{{ $blueprint['site_name'] ?? 'Site' }}</div>
      </header>

      <div class="cyb-thread">
        @includeIf("instasites.themes.$themeName.partials.hero")
        @yield('content')
        @includeIf("instasites.themes.$themeName.partials.trust")
      </div>

      @includeIf("instasites.themes.$themeName.partials.footer")
    </section>
  </main>

  @includeIf("instasites.themes.$themeName.partials.popup")
  @includeIf("instasites.themes.$themeName.partials.lang-switcher")
</body>
</html>

@php $themeName = strtolower($blueprint['theme']['name'] ?? 'general'); @endphp
{{-- Bootstrap, vendor CSS, etc. should come first --}}
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="/assets/{{ $themeName }}/style.css">
<link rel="stylesheet" href="/assets/{{ $themeName }}/vars.css">
@includeIf("instasites.themes.$themeName.partials.header")
@includeIf("instasites.themes.$themeName.partials.hero")
<main class="gen-main container">
  <div class="gen-card">@yield('content')</div>
  @includeIf("instasites.themes.$themeName.partials.features")
</main>
@includeIf("instasites.themes.$themeName.partials.footer")
@includeIf("instasites.themes.$themeName.partials.lang-switcher")

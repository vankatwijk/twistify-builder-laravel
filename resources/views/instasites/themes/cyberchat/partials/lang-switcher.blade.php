@php
  $langs = ($blueprint['theme']['languages'] ?? []) ?: (($locale ?? null) && ($defaultLocale ?? null) ? [$defaultLocale, $locale] : []);
@endphp

@if(!empty($langs) && count($langs) > 1)
  <div class="gen-lang" role="navigation" aria-label="Language switcher">
    @foreach($langs as $code)
      @php $active = ($code === ($locale ?? '')); @endphp
      <a class="gen-pill {{ $active ? 'active' : '' }}" href="{{ url("/$code/") }}">{{ strtoupper($code) }}</a>
    @endforeach
  </div>
@endif

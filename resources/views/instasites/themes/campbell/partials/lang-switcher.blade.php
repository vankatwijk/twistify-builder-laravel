@php
  $langs = $availableLocales ?? ($locales ?? ['en']);
  $cur   = $locale ?? ($blueprint['default_locale'] ?? 'en');
@endphp

@if(is_array($langs) && count($langs) > 1)
  <div class="gen-lang">
    @foreach($langs as $lc)
      <a class="gen-pill {{ $lc===$cur?'active':'' }}" href="{{ url()->current() }}?lang={{ $lc }}">{{ strtoupper($lc) }}</a>
    @endforeach
  </div>
@endif

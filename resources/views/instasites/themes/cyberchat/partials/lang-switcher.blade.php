
@if(!empty($locales ?? []) && count($locales) > 1)
  <div class="gen-lang" role="navigation" aria-label="Language switcher">
    @foreach($locales as $code)
      @php $active = ($code === ($locale ?? '')); @endphp
      <a class="gen-pill {{ $active ? 'active' : '' }}" href="{{ $code === ($defaultLocale ?? 'en') ? '/' : "/$code/" }}">{{ strtoupper($code) }}</a>
    @endforeach
  </div>
@endif

@if(!empty($locales) && count($locales) > 1)
  <div class="cyb-lang">
    @foreach($locales as $loc)
      <a class="cyb-pill {{ $loc === $locale ? 'active' : '' }}"
         href="{{ $loc === $defaultLocale ? '/' : "/$loc/" }}">
        {{ strtoupper($loc) }}
      </a>
    @endforeach
  </div>
@endif

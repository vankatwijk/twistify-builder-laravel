@if(!empty($locales) && count($locales) > 1)
  <div class="gen-lang">
    @foreach($locales as $loc)
      <a class="gen-pill {{ $loc === $locale ? 'active' : '' }}"
         href="{{ $loc === $defaultLocale ? '/' : "/$loc/" }}">{{ strtoupper($loc) }}</a>
    @endforeach
  </div>
@endif
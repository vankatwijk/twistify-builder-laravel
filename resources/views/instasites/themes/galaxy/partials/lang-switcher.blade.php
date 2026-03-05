@if(!empty($locales) && count($locales) > 1)
  <div class="galaxy-lang">
    @foreach($locales as $loc)
      <a class="galaxy-pill {{ $loc === $locale ? 'active' : '' }}" href="{{ $loc === $defaultLocale ? '/' : "/$loc/" }}">{{ strtoupper($loc) }}</a>
    @endforeach
  </div>
@endif

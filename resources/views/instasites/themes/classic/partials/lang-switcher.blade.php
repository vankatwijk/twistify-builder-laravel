@if(!empty($locales) && count($locales) > 1)
  <div class="lm-lang-switcher" style="position:fixed;right:16px;bottom:16px">
    @foreach($locales as $loc)
      <a href="{{ $loc === $defaultLocale ? '/' : "/$loc/" }}" class="btn btn-sm btn-outline-secondary mx-1">
        {{ strtoupper($loc) }}
      </a>
    @endforeach
  </div>
@endif

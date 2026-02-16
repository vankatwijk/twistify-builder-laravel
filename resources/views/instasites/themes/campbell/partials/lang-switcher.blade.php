
@if(!empty($locales ?? []) && count($locales) > 1)
  <div class="gen-lang">
    @foreach($locales as $lc)
      @php $active = ($lc === ($locale ?? '')); @endphp
      <a class="gen-pill {{ $active ? 'active' : '' }}" href="{{ $lc === ($defaultLocale ?? 'en') ? '/' : "/$lc/" }}">{{ strtoupper($lc) }}</a>
    @endforeach
  </div>
@endif

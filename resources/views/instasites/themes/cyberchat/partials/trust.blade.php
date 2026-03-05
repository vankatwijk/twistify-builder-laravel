@php
  $trust = $blueprint['theme']['trust'] ?? [];
  $enabled = !empty($trust['enabled']);
  $logos = is_array($trust['logos'] ?? null) ? $trust['logos'] : [];
@endphp

@if($enabled)
<section class="gen-trust" aria-label="Trust indicators">
  <div class="gen-trust-inner">
    <div class="gen-trust-copy">
      @if(!empty($trust['ratingText']))<span>{{ $trust['ratingText'] }}</span>@endif
      @if(!empty($trust['statsText']))<span>{{ $trust['statsText'] }}</span>@endif
    </div>
    @if(!empty($logos))
      <div class="gen-trust-logos">
        @foreach($logos as $logo)
          <img src="{{ $logo }}" alt="Trust logo" loading="lazy">
        @endforeach
      </div>
    @endif
  </div>
</section>
@endif

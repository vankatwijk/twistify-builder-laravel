@php
  $trust = $blueprint['theme']['trust'] ?? [];
  $enabled = !empty($trust['enabled']);
  $logos = is_array($trust['logos'] ?? null) ? $trust['logos'] : [];
@endphp

@if($enabled)
  <section class="classic-trust" aria-label="Trust indicators">
    <div class="container classic-trust-inner">
      <div class="classic-trust-copy">
        @if(!empty($trust['ratingText']))
          <p>{{ $trust['ratingText'] }}</p>
        @endif
        @if(!empty($trust['statsText']))
          <p>{{ $trust['statsText'] }}</p>
        @endif
      </div>
      @if(!empty($logos))
        <div class="classic-trust-logos">
          @foreach($logos as $logo)
            <img src="{{ $logo }}" alt="Trust logo" loading="lazy">
          @endforeach
        </div>
      @endif
    </div>
  </section>
@endif

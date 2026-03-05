@php
  $popup = $blueprint['theme']['popup'] ?? [];
  $enabled = !empty($popup['enabled']);
  $ctaUrl = $popup['ctaUrl'] ?? '#';
@endphp

@if($enabled)
  <div class="gen-popup" role="dialog" aria-label="Promotional popup">
    <button type="button" class="gen-popup-close" aria-label="Close popup" onclick="this.closest('.gen-popup').style.display='none'">×</button>

    @if(!empty($popup['imageUrl']))
      <div class="gen-popup-media">
        <img src="{{ $popup['imageUrl'] }}" alt="Popup" loading="lazy">
        <div class="gen-popup-overlay">
          @if(!empty($popup['text']))<p>{{ $popup['text'] }}</p>@endif
          @if(!empty($popup['ctaText']))<a href="{{ $ctaUrl }}" class="gen-popup-cta">{{ $popup['ctaText'] }}</a>@endif
        </div>
      </div>
    @else
      <div class="gen-popup-card">
        @if(!empty($popup['text']))<p>{{ $popup['text'] }}</p>@endif
        @if(!empty($popup['ctaText']))<a href="{{ $ctaUrl }}" class="gen-popup-cta">{{ $popup['ctaText'] }}</a>@endif
      </div>
    @endif
  </div>
@endif

@php
  $popup = $blueprint['theme']['popup'] ?? [];
  $enabled = !empty($popup['enabled']);
  $ctaUrl = $popup['ctaUrl'] ?? '#';
@endphp

@if($enabled)
  <div class="galaxy-popup" role="dialog" aria-label="Promotional popup">
    <button type="button" class="galaxy-popup-close" aria-label="Close popup" onclick="this.closest('.galaxy-popup').style.display='none'">×</button>

    @if(!empty($popup['imageUrl']))
      <div class="galaxy-popup-media">
        <img src="{{ $popup['imageUrl'] }}" alt="Popup" loading="lazy">
        <div class="galaxy-popup-overlay">
          @if(!empty($popup['text']))<p>{{ $popup['text'] }}</p>@endif
          @if(!empty($popup['ctaText']))<a href="{{ $ctaUrl }}" class="galaxy-popup-cta">{{ $popup['ctaText'] }}</a>@endif
        </div>
      </div>
    @else
      <div class="galaxy-popup-card">
        @if(!empty($popup['text']))<p>{{ $popup['text'] }}</p>@endif
        @if(!empty($popup['ctaText']))<a href="{{ $ctaUrl }}" class="galaxy-popup-cta">{{ $popup['ctaText'] }}</a>@endif
      </div>
    @endif
  </div>
@endif

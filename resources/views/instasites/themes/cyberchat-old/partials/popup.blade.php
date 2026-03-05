@php
  $popup = $blueprint['theme']['popup'] ?? [];
  $enabled = !empty($popup['enabled']);
  $ctaUrl = $popup['ctaUrl'] ?? '#';
@endphp

@if($enabled)
  <div class="cyb-popup" role="dialog" aria-label="Promotional popup">
    <button type="button" class="cyb-popup-close" aria-label="Close popup" onclick="this.closest('.cyb-popup').style.display='none'">×</button>

    @if(!empty($popup['imageUrl']))
      <div class="cyb-popup-media">
        <img src="{{ $popup['imageUrl'] }}" alt="Popup" loading="lazy">
        <div class="cyb-popup-overlay">
          @if(!empty($popup['text']))<p>{{ $popup['text'] }}</p>@endif
          @if(!empty($popup['ctaText']))<a href="{{ $ctaUrl }}" class="cyb-popup-cta">{{ $popup['ctaText'] }}</a>@endif
        </div>
      </div>
    @else
      <div class="cyb-popup-card">
        @if(!empty($popup['text']))<p>{{ $popup['text'] }}</p>@endif
        @if(!empty($popup['ctaText']))<a href="{{ $ctaUrl }}" class="cyb-popup-cta">{{ $popup['ctaText'] }}</a>@endif
      </div>
    @endif
  </div>
@endif

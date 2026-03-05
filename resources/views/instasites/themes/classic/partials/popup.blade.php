@php
  $popup = $blueprint['theme']['popup'] ?? [];
  $enabled = !empty($popup['enabled']);
  $ctaUrl = $popup['ctaUrl'] ?? '#';
@endphp

@if($enabled)
  <div class="classic-popup" role="dialog" aria-label="Promotional popup">
    <button type="button" class="classic-popup-close" aria-label="Close popup" onclick="this.closest('.classic-popup').style.display='none'">×</button>

    @if(!empty($popup['imageUrl']))
      <div class="classic-popup-media">
        <img src="{{ $popup['imageUrl'] }}" alt="Popup" loading="lazy">
        <div class="classic-popup-overlay">
          @if(!empty($popup['text']))<p>{{ $popup['text'] }}</p>@endif
          @if(!empty($popup['ctaText']))<a href="{{ $ctaUrl }}" class="classic-popup-cta">{{ $popup['ctaText'] }}</a>@endif
        </div>
      </div>
    @else
      <div class="classic-popup-card">
        @if(!empty($popup['text']))<p>{{ $popup['text'] }}</p>@endif
        @if(!empty($popup['ctaText']))<a href="{{ $ctaUrl }}" class="classic-popup-cta">{{ $popup['ctaText'] }}</a>@endif
      </div>
    @endif
  </div>
@endif

@php
  $navCta = $blueprint['theme']['nav']['cta'] ?? [];
  $ctaText = $navCta['text'] ?? 'New Project';
  $ctaHref = $navCta['href'] ?? '#';

  $title = $blueprint['theme']['hero']['headline'] ?? 'Chat-style, clean, and fast.';
  $sub   = $blueprint['theme']['hero']['subheadline'] ?? 'A minimal, dark UI with emerald accents—optimized for readability and speed.';
  $cta1  = ['text' => ($blueprint['theme']['hero']['ctaText'] ?? $ctaText), 'href' => ($blueprint['theme']['hero']['ctaHref'] ?? $ctaHref)];
  $cta2  = ['text' => ($blueprint['theme']['hero']['secondaryCtaText'] ?? 'Docs'), 'href' => ($blueprint['theme']['hero']['secondaryCtaHref'] ?? '#features')];
  $heroImage = $blueprint['theme']['hero']['imageUrl'] ?? null;
@endphp

<section class="cyb-hero-msg" aria-label="Intro">
  <div class="cyb-msg assistant">
    <div class="cyb-avatar">AI</div>
    <div class="cyb-msg-body">
      <h1 class="cyb-msg-title">{{ $title }}</h1>
      <p class="cyb-msg-sub">{{ $sub }}</p>
      <div class="cyb-msg-actions">
        <a class="cyb-btn primary" href="{{ $cta1['href'] }}">{{ $cta1['text'] }}</a>
        <a class="cyb-btn" href="{{ $cta2['href'] }}">{{ $cta2['text'] }}</a>
      </div>

      @if(!empty($heroImage))
        <div class="cyb-msg-image">
          <img src="{{ $heroImage }}" alt="{{ $blueprint['site_name'] ?? 'Hero' }}" loading="lazy">
        </div>
      @endif
    </div>
  </div>
</section>

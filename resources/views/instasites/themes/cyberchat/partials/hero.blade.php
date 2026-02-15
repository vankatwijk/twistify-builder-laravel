@php
  $ctaText = $blueprint['theme']['cta']['text'] ?? 'New Project';
  $ctaHref = $blueprint['theme']['cta']['href'] ?? '#';

  $title = $blueprint['theme']['hero']['title'] ?? 'Chat-style, clean, and fast.';
  $sub   = $blueprint['theme']['hero']['subtitle'] ?? 'A minimal, dark UI with emerald accents—optimized for readability and speed.';
  $cta1  = $blueprint['theme']['hero']['cta_primary'] ?? ['text' => $ctaText, 'href' => $ctaHref];
  $cta2  = $blueprint['theme']['hero']['cta_secondary'] ?? ['text' => 'Docs', 'href' => '#features'];
@endphp

<section class="gen-hero">
  <div class="container gen-hero-inner">
    <div>
      <h1 class="gen-hero-title">{{ $title }}</h1>
      <p class="gen-hero-sub">{{ $sub }}</p>
      <div class="gen-cta-row">
        <a class="gen-hero-cta" href="{{ $cta1['href'] }}">{{ $cta1['text'] }}</a>
        <a class="gen-hero-cta ghost" href="{{ $cta2['href'] }}">{{ $cta2['text'] }}</a>
      </div>
    </div>
    <div class="gen-hero-art">
      {{-- Optional artwork slot --}}
      {{-- <img src="{{ $blueprint['theme']['hero']['image'] ?? '/assets/cyberchat/hero.png' }}" alt=""> --}}
    </div>
  </div>
</section>

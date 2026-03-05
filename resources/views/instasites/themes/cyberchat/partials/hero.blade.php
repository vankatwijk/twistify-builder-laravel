@php
  $navCta = $blueprint['theme']['nav']['cta'] ?? [];
  $ctaText = $navCta['text'] ?? 'New Project';
  $ctaHref = $navCta['href'] ?? '#';

  $title = $blueprint['theme']['hero']['headline'] ?? 'Chat-style, clean, and fast.';
  $sub   = $blueprint['theme']['hero']['subheadline'] ?? 'A minimal, dark UI with emerald accents—optimized for readability and speed.';
  $cta1  = ['text' => ($blueprint['theme']['hero']['ctaText'] ?? $ctaText), 'href' => ($blueprint['theme']['hero']['ctaHref'] ?? $ctaHref)];
  $cta2  = ['text' => ($blueprint['theme']['hero']['secondaryCtaText'] ?? 'Docs'), 'href' => ($blueprint['theme']['hero']['secondaryCtaHref'] ?? '#features')];
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

@php
  $hero = $blueprint['theme']['hero'] ?? null;
@endphp
@if($hero)
<section class="gen-hero">
  <div class="container gen-hero-inner">
    <div class="gen-hero-copy">
      <h1 class="gen-hero-title">{{ $hero['title'] ?? ($blueprint['site_name'] ?? 'Welcome') }}</h1>
      @if(!empty($hero['subtitle']))
        <p class="gen-hero-sub">{{ $hero['subtitle'] }}</p>
      @endif
      @if(!empty($hero['cta']))
        <a class="gen-hero-cta" href="{{ $hero['cta']['href'] ?? '#' }}">{{ $hero['cta']['text'] ?? 'Get Started' }}</a>
      @endif
      @if(!empty($hero['badges']) && is_array($hero['badges']))
        <div class="gen-badges">
          @foreach($hero['badges'] as $b)
            <div class="gen-badge">{{ $b }}</div>
          @endforeach
        </div>
      @endif
    </div>
    @if(!empty($hero['image']))
      <div class="gen-hero-art">
        <img src="{{ $hero['image'] }}" alt="" loading="lazy">
      </div>
    @endif
  </div>
</section>
@endif

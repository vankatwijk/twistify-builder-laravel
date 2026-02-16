@if(!empty($heroMedia))
<section class="classic-hero">
  <div class="container classic-hero-inner">
    <div class="classic-hero-content">
      <h1>{{ $metaTitle ?? ($blueprint['site_name'] ?? 'Site') }}</h1>
      @if(!empty($metaDescription))
        <p class="classic-hero-subtitle">{{ $metaDescription }}</p>
      @endif
    </div>
    <div class="classic-hero-image">
      <img src="{{ $heroMedia }}" alt="{{ $blueprint['site_name'] ?? 'Hero' }}" onerror="this.style.display='none'">
    </div>
  </div>
</section>
@endif

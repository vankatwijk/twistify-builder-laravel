@php $themeName = strtolower($blueprint['theme']['name'] ?? 'general'); @endphp
@php
  $ctaText = $blueprint['theme']['cta']['text'] ?? 'New Project';
  $ctaHref = $blueprint['theme']['cta']['href'] ?? '#';

  $title = $blueprint['theme']['hero']['title'] ?? 'Chat-style, clean, and fast.';
  $sub   = $blueprint['theme']['hero']['subtitle'] ?? 'A minimal, dark UI with emerald accents—optimized for readability and speed.';
  $cta1  = $blueprint['theme']['hero']['cta_primary'] ?? ['text' => $ctaText, 'href' => $ctaHref];
  $cta2  = $blueprint['theme']['hero']['cta_secondary'] ?? ['text' => 'Docs', 'href' => '#features'];
@endphp
{{-- Bootstrap, vendor CSS, etc. should come first --}}
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="icon" href="/assets/favicon.png" type="image/png">
<link rel="stylesheet" href="/assets/{{ $themeName }}/vars.css">
<link rel="stylesheet" href="/assets/{{ $themeName }}/style.css">

<style>
  /* Hero section animations and enhanced styling */
  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-12px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 20px rgba(16,163,127,.3), 0 0 40px rgba(0,212,255,.1); }
    50% { box-shadow: 0 0 30px rgba(16,163,127,.5), 0 0 60px rgba(0,212,255,.2); }
  }
  
  .gen-hero-section {
    background: linear-gradient(135deg, rgba(16,163,127,.08) 0%, rgba(0,212,255,.04) 100%);
    border: 1px solid rgba(16,163,127,.2);
    border-radius: 16px;
    padding: 40px 28px;
    margin: 0 auto 32px;
    max-width: 760px;
    text-align: center;
    animation: fadeInDown 0.6s ease-out;
  }
  
  .gen-hero-section h1 {
    font-size: var(--fs-h1);
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 16px;
    line-height: 1.1;
    background: linear-gradient(135deg, var(--text) 0%, var(--primary-light) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: fadeInDown 0.7s ease-out 0.1s both;
  }
  
  .gen-hero-section p {
    font-size: 1.1rem;
    color: var(--text-light);
    margin: 0 0 24px;
    max-width: 580px;
    margin-left: auto;
    margin-right: auto;
    animation: fadeInUp 0.7s ease-out 0.2s both;
  }
  
  .gen-hero-ctas {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    animation: fadeInUp 0.7s ease-out 0.3s both;
  }
  
  .gen-hero-ctas a {
    padding: 12px 28px;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  
  .gen-cta-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #0c1215;
    border: 1px solid rgba(16,163,127,.6);
    box-shadow: 0 8px 24px rgba(16,163,127,.3);
  }
  
  .gen-cta-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(16,163,127,.5);
  }
  
  .gen-cta-secondary {
    background: rgba(16,163,127,.12);
    color: var(--text);
    border: 1px solid rgba(16,163,127,.4);
  }
  
  .gen-cta-secondary:hover {
    background: rgba(16,163,127,.2);
    border-color: rgba(16,163,127,.6);
    transform: translateY(-2px);
  }
  
  .gen-hero-media {
    margin-top: 28px;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(16,163,127,.2);
    box-shadow: 0 12px 40px rgba(0,0,0,.4);
    animation: pulse-glow 4s ease-in-out infinite;
  }
  
  .gen-hero-media img {
    display: block;
    width: 100%;
    height: auto;
    transition: transform 0.4s ease;
  }
  
  .gen-hero-media:hover img {
    transform: scale(1.03);
  }
  
  .gen-hero-art { display: none; }
</style>

<main class="gen-main container">
  @includeIf("instasites.themes.$themeName.partials.sidebar")   {{-- <— Add this --}}

  <div class="gen-card">
    <div class="chat-pane-head">
      <div class="muted">{{ $blueprint['site_name'] ?? 'Site' }}</div>

    </div>

    <div class="chat-scroll">
      {{-- Example messages mapped from your CMS --}}
      <div class="bubble user">
        <span class="avatar" aria-hidden="true"></span>
        <div class="content">
          <p>Show me the latest SEO checklist.</p>
        </div>
      </div>

      <div class="bubble assistant">
        <span class="avatar" aria-hidden="true"></span>
        <div class="content">
          <p>Here’s a concise checklist to get you started:</p>
          <ul>
            <li>Fix crawl errors</li>
            <li>Optimize titles & meta</li>
            <li>Improve Core Web Vitals</li>
          </ul>
        </div>
      </div>

      <div class="gen-hero-art">
        @if(!empty($heroMedia))
          <img src="{{ $heroMedia }}" alt="{{ $blueprint['site_name'] ?? 'Hero' }}" onerror="this.style.display='none'">
        @elseif(!empty($assets['assetsCopied'])) 
          {{-- Fallback (optional) --}}
          <img src="/assets/campbell/placeholder-headshot.jpg" alt="Placeholder" onerror="this.style.display='none'">
        @endif
      </div>

      {{-- Render your post/page blocks as bubbles if you want --}}
      @yield('content')
    </div>
  </div>


  @includeIf("instasites.themes.$themeName.partials.features")
</main>

@includeIf("instasites.themes.$themeName.partials.footer")
@includeIf("instasites.themes.$themeName.partials.lang-switcher")

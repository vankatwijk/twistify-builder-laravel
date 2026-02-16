@php
  $bp       = $blueprint ?? [];
  $siteName = $bp['site_name'] ?? 'Site';
  $year     = date('Y');

  // Build social links from the new format (facebook, linkedin, x, threads, instagram)
  $socialMapping = [
    'facebook'  => ['label' => 'Facebook', 'icon' => 'f'],
    'linkedin'  => ['label' => 'LinkedIn', 'icon' => 'in'],
    'x'         => ['label' => 'X', 'icon' => 'x'],
    'threads'   => ['label' => 'Threads', 'icon' => '@'],
    'instagram' => ['label' => 'Instagram', 'icon' => '📷'],
  ];

  $social = [];
  $themeSocial = $bp['theme']['social'] ?? [];
  if (is_array($themeSocial) && !empty($themeSocial)) {
    foreach ($socialMapping as $key => $info) {
      if (!empty($themeSocial[$key])) {
        $social[] = [
          'label' => $info['label'],
          'href' => $themeSocial[$key]
        ];
      }
    }
  }

  // Fallback to old format if no social data
  if (empty($social) && isset($bp['social'])) {
    $social = is_array($bp['social']) ? $bp['social'] : [];
  }

  // Ultimate fallback
  if (empty($social)) {
    $social = [
      ['label'=>'YouTube','href'=>'#'],
      ['label'=>'Twitter/X','href'=>'#'],
      ['label'=>'LinkedIn','href'=>'#'],
    ];
  }

  // Recent posts come from builder; gracefully handle missing var
  $recent = (isset($recentPosts) && is_iterable($recentPosts)) ? $recentPosts : [];
@endphp

@if(!empty($recent))
  <section class="container gen-features" style="margin-top:32px">
    @foreach($recent as $p)
      @php
        $title = $p['title'] ?? 'Post';
        $href  = $p['href']  ?? '#';
        $desc  = $p['desc']  ?? '';
      @endphp
      <a class="gen-feature" href="{{ $href }}">
        <h3 class="m-0">{{ $title }}</h3>
        @if($desc)<p class="m-0 muted">{{ $desc }}</p>@endif
      </a>
    @endforeach
  </section>
@endif

<footer class="gen-footer">
  <div class="container gen-footer-inner">
    <div class="muted">© {{ $year }} {{ $siteName }} — All rights reserved.</div>
    <div class="gen-menu">
      @foreach($social as $s)
        @php
          $lbl = is_array($s) ? ($s['label'] ?? '') : '';
          $url = is_array($s) ? ($s['href'] ?? '#') : '#';
        @endphp
        @if($lbl)
          <a class="gen-link" href="{{ $url }}" target="_blank" rel="noopener">{{ $lbl }}</a>
        @endif
      @endforeach
    </div>
  </div>
</footer>

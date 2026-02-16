@php
  $bp = $blueprint ?? [];
  $siteName = $bp['site_name'] ?? 'Site';
  $year = date('Y');

  // Build social links from the new format (facebook, linkedin, x, threads, instagram)
  $socialMapping = [
    'facebook'  => ['label' => 'Facebook'],
    'linkedin'  => ['label' => 'LinkedIn'],
    'x'         => ['label' => 'X'],
    'threads'   => ['label' => 'Threads'],
    'instagram' => ['label' => 'Instagram'],
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
@endphp

<footer class="gen-footer">
  <div class="container gen-footer-inner">
    <span class="muted">© {{ $year }} {{ $siteName }}</span>
    <span class="muted">Built with InstaSites</span>
    @if(!empty($social))
      <div class="gen-menu" style="gap: 16px;">
        @foreach($social as $s)
          <a class="gen-link" href="{{ $s['href'] }}" target="_blank" rel="noopener">{{ $s['label'] }}</a>
        @endforeach
      </div>
    @endif
  </div>
</footer>

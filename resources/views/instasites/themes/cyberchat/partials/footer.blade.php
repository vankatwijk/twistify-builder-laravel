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

<footer class="cyb-footer">
  <div class="cyb-footer-inner">
    <span class="cyb-muted">© {{ $year }} {{ $siteName }}</span>
    @php $compliance = $bp['theme']['footer']['compliance'] ?? []; @endphp
    @if(!empty($compliance['show18Plus']) || !empty($compliance['disclaimerText']))
      <span class="cyb-muted">
        @if(!empty($compliance['show18Plus']))18+ · @endif
        {{ $compliance['disclaimerText'] ?? 'Play responsibly.' }}
        @if(!empty($compliance['responsibleLink']))
          <a class="cyb-link" href="{{ $compliance['responsibleLink'] }}" target="_blank" rel="noopener">Help</a>
        @endif
      </span>
    @else
      <span class="cyb-muted">Built with InstaSites</span>
    @endif
    @if(!empty($social))
      <div class="cyb-footer-social">
        @foreach($social as $s)
          <a class="cyb-link" href="{{ $s['href'] }}" target="_blank" rel="noopener">{{ $s['label'] }}</a>
        @endforeach
      </div>
    @endif
  </div>
</footer>

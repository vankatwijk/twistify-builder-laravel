<footer>
  @php
    $bp = $blueprint ?? [];
    $siteName = $bp['site_name'] ?? 'Site';
    
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

    // Fallback to old format if no social data
    if (empty($social) && isset($bp['socialLinks'])) {
      $social = is_array($bp['socialLinks']) ? $bp['socialLinks'] : [];
    }
  @endphp
  <div class="container">
    <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
    @if(!empty($social))
      <div>
        @foreach($social as $s)
          <a href="{{ $s['href'] ?? '#' }}" target="_blank" rel="noopener">
            {{ $s['label'] ?? 'Link' }}
          </a>
        @endforeach
      </div>
    @endif
  </div>
</footer>
@php
  $bp = $blueprint ?? [];
  $compliance = $bp['theme']['footer']['compliance'] ?? [];
  $social = $bp['theme']['social'] ?? [];
  $socialMap = [
    'facebook' => 'Facebook',
    'linkedin' => 'LinkedIn',
    'x' => 'X',
    'threads' => 'Threads',
    'instagram' => 'Instagram',
  ];
@endphp

<footer class="galaxy-footer">
  <div class="container galaxy-footer-inner">
    <p>© {{ date('Y') }} {{ $bp['site_name'] ?? 'Site' }}</p>

    @if(!empty($compliance['show18Plus']) || !empty($compliance['disclaimerText']))
      <p>
        @if(!empty($compliance['show18Plus']))18+ · @endif
        {{ $compliance['disclaimerText'] ?? 'Play responsibly.' }}
        @if(!empty($compliance['responsibleLink']))
          <a href="{{ $compliance['responsibleLink'] }}" target="_blank" rel="noopener">Help</a>
        @endif
      </p>
    @endif

    <div class="galaxy-social">
      @foreach($socialMap as $key => $label)
        @if(!empty($social[$key]))
          <a href="{{ $social[$key] }}" target="_blank" rel="noopener">{{ $label }}</a>
        @endif
      @endforeach
    </div>
  </div>
</footer>

<footer class="gen-footer">
  <div class="container gen-footer-inner">
    <div class="muted">© {{ date('Y') }} {{ $blueprint['site_name'] ?? 'Site' }}</div>
    @php $compliance = $blueprint['theme']['footer']['compliance'] ?? []; @endphp
    @if(!empty($compliance['show18Plus']) || !empty($compliance['disclaimerText']))
      <div class="muted">
        @if(!empty($compliance['show18Plus']))18+ · @endif
        {{ $compliance['disclaimerText'] ?? 'Play responsibly.' }}
        @if(!empty($compliance['responsibleLink']))
          <a href="{{ $compliance['responsibleLink'] }}" target="_blank" rel="noopener">Help</a>
        @endif
      </div>
    @else
      <div class="muted">Built with Instasites</div>
    @endif
  </div>
</footer>
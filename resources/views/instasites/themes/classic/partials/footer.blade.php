<footer>
  <div class="container">
    <p>&copy; {{ date('Y') }} {{ $blueprint['site_name'] ?? 'Site' }}. All rights reserved.</p>
    @if(!empty($blueprint['theme']['socialLinks']))
      <div>
        @foreach($blueprint['theme']['socialLinks'] as $social)
          <a href="{{ $social['url'] ?? '#' }}" target="_blank" rel="noopener">
            {{ $social['label'] ?? 'Link' }}
          </a>
        @endforeach
      </div>
    @endif
  </div>
</footer>
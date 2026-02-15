@php
  // Accept from controller or blueprint; fallback to navItems
  $chatItems = $chatItems
    ?? ($blueprint['theme']['chat_items'] ?? null)
    ?? array_map(fn($it) => [
      'title'=>$it['title'] ?? 'Page', 'href'=>$it['href'] ?? '#', 'meta'=>null
    ], ($navItems ?? []));

  $newText = $blueprint['theme']['cta']['text'] ?? 'New';
  $newHref = $blueprint['theme']['cta']['href'] ?? '#';
@endphp

<aside class="gen-side" aria-label="Sidebar">
  {{-- Mobile drawer --}}
  <details class="side-mobile">
    <summary class="side-toggle">
      <span class="burger"></span><span class="burger"></span><span class="burger"></span>
      <span>Menu</span>
    </summary>
    <div class="side-panel">
      <div class="side-top">
        <a href="{{ $newHref }}" class="side-new">{{ $newText }}</a>
      </div>
      <nav class="chat-list" aria-label="Pages & Posts">
        @forelse($chatItems as $it)
          <a class="chat-item" href="{{ $it['href'] }}">
            <span class="chat-dot"></span>
            <span class="chat-title">{{ $it['title'] }}</span>
            @if(!empty($it['meta']))<span class="chat-meta">{{ $it['meta'] }}</span>@endif
          </a>
        @empty
          <span class="chat-empty muted">No items yet</span>
        @endforelse
      </nav>
      <div class="side-bottom">
        @foreach(($blueprint['theme']['sidebar_links'] ?? []) as $l)
          <a class="side-link" href="{{ $l['href'] ?? '#' }}">{{ $l['title'] ?? 'Link' }}</a>
        @endforeach
      </div>
    </div>
  </details>

  {{-- Desktop rail --}}
  <div class="side-desktop">
    <div class="side-top">
      <a href="{{ $newHref }}" class="side-new">{{ $newText }}</a>
    </div>
    <nav class="chat-list" aria-label="Pages & Posts">
      @forelse($chatItems as $it)
        <a class="chat-item" href="{{ $it['href'] }}">
          <span class="chat-dot"></span>
          <span class="chat-title">{{ $it['title'] }}</span>
          @if(!empty($it['meta']))<span class="chat-meta">{{ $it['meta'] }}</span>@endif
        </a>
      @empty
        <span class="chat-empty muted">No items yet</span>
      @endforelse
    </nav>
    <div class="side-bottom">
      @foreach(($blueprint['theme']['sidebar_links'] ?? []) as $l)
        <a class="side-link" href="{{ $l['href'] ?? '#' }}">{{ $l['title'] ?? 'Link' }}</a>
      @endforeach
    </div>
  </div>
</aside>

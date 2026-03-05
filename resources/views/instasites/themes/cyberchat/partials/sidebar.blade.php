@php
  $chatItems = $chatItems
    ?? ($blueprint['theme']['chat_items'] ?? null)
    ?? array_map(fn($it) => [
      'title'=>$it['title'] ?? 'Page', 'href'=>$it['href'] ?? '#', 'meta'=>null
    ], ($navItems ?? []));

  $navCta = $blueprint['theme']['nav']['cta'] ?? [];
  $newText = $navCta['text'] ?? 'New';
  $newHref = $navCta['href'] ?? '#';
@endphp

<aside class="cyb-side" aria-label="Conversation history">
  <details class="cyb-side-mobile">
    <summary class="cyb-side-toggle" aria-label="Open navigation">
      <span class="cyb-burger"></span><span class="cyb-burger"></span><span class="cyb-burger"></span>
      <span>Pages</span>
    </summary>

    <div class="cyb-side-panel">
      <div class="cyb-side-head">
        <a href="{{ $newHref }}" class="cyb-new-chat">{{ $newText }}</a>
      </div>

      <nav class="cyb-history" aria-label="Pages and posts">
        @forelse($chatItems as $it)
          <a class="cyb-history-item" href="{{ $it['href'] }}" title="{{ $it['title'] }}">
            <span class="cyb-history-title">{{ $it['title'] }}</span>
            @if(!empty($it['meta']))<span class="cyb-history-meta">{{ $it['meta'] }}</span>@endif
          </a>
        @empty
          <span class="cyb-history-empty">No pages yet</span>
        @endforelse
      </nav>

      <div class="cyb-side-links">
        @foreach(($blueprint['theme']['sidebar_links'] ?? []) as $l)
          <a class="cyb-side-link" href="{{ $l['href'] ?? '#' }}">{{ $l['title'] ?? 'Link' }}</a>
        @endforeach
      </div>
    </div>
  </details>

  <div class="cyb-side-desktop">
    <div class="cyb-side-head">
      <a href="{{ $newHref }}" class="cyb-new-chat">{{ $newText }}</a>
    </div>

    <nav class="cyb-history" aria-label="Pages and posts">
      @forelse($chatItems as $it)
        <a class="cyb-history-item" href="{{ $it['href'] }}" title="{{ $it['title'] }}">
          <span class="cyb-history-title">{{ $it['title'] }}</span>
          @if(!empty($it['meta']))<span class="cyb-history-meta">{{ $it['meta'] }}</span>@endif
        </a>
      @empty
        <span class="cyb-history-empty">No pages yet</span>
      @endforelse
    </nav>

    <div class="cyb-side-links">
      @foreach(($blueprint['theme']['sidebar_links'] ?? []) as $l)
        <a class="cyb-side-link" href="{{ $l['href'] ?? '#' }}">{{ $l['title'] ?? 'Link' }}</a>
      @endforeach
    </div>
  </div>
</aside>

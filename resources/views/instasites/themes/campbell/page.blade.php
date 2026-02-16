@extends($layout_view)

@section('content')
  {!! $contentHtml !!}

  {{-- Display last 3 blog posts at the end of the page --}}
  @php
    $recent = (isset($recentPosts) && is_iterable($recentPosts)) ? array_slice($recentPosts, 0, 3) : [];
  @endphp

  @if(!empty($recent))
    <section class="recent-posts-section">
      <div class="container">
        <h2>Latest Blog Posts</h2>
        <div class="recent-posts-grid">
          @foreach($recent as $p)
            <article class="recent-post-card">
              <h3 class="recent-post-title">
                <a href="{{ $p['href'] ?? '#' }}">{{ $p['title'] ?? 'Post' }}</a>
              </h3>
              @if(!empty($p['date']))
                <div class="recent-post-meta">{{ \Illuminate\Support\Carbon::parse($p['date'])->format('F j, Y') }}</div>
              @endif
              @if(!empty($p['desc']))
                <p class="recent-post-excerpt">{{ $p['desc'] }}</p>
              @endif
              <a href="{{ $p['href'] ?? '#' }}" class="recent-post-link">Read more →</a>
            </article>
          @endforeach
        </div>
      </div>
    </section>
  @endif
@endsection

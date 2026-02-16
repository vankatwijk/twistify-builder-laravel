@extends($layout_view)

@section('content')
  <div class="container gen-main">
    <h1 class="gen-h1">{{ $title ?? 'Blog' }}</h1>
    @if(!empty($postsList))
      <div class="blog-posts">
        @foreach($postsList as $p)
          <article class="post-card">
            <div class="post-card-content">
              <h2 class="post-card-title">
                <a href="{{ $p['href'] ?? '#' }}">{{ $p['title'] ?? 'Post' }}</a>
              </h2>
              @if(!empty($p['date']))
                <div class="post-meta">{{ \Illuminate\Support\Carbon::parse($p['date'])->format('F j, Y') }}</div>
              @endif
              @if(!empty($p['desc']))
                <p class="post-excerpt">{{ $p['desc'] }}</p>
              @endif
              <a href="{{ $p['href'] ?? '#' }}" class="read-more">Read more →</a>
            </div>
          </article>
        @endforeach
      </div>
    @else
      <p>No posts yet.</p>
    @endif
  </div>
@endsection
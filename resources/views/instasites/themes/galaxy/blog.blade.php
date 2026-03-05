@extends($layout_view)

@section('content')
  <h1>{{ $title ?? 'Blog' }}</h1>
  @if(!empty($postsList))
    <div class="galaxy-cards">
      @foreach($postsList as $p)
        <article class="galaxy-card">
          <h2><a href="{{ $p['href'] ?? '#' }}">{{ $p['title'] ?? 'Post' }}</a></h2>
          @if(!empty($p['date']))
            <p class="galaxy-meta">{{ \Illuminate\Support\Carbon::parse($p['date'])->format('F j, Y') }}</p>
          @endif
          @if(!empty($p['desc']))<p>{{ $p['desc'] }}</p>@endif
          <a class="galaxy-readmore" href="{{ $p['href'] ?? '#' }}">Read more →</a>
        </article>
      @endforeach
    </div>
  @else
    <p>No posts yet.</p>
  @endif
@endsection

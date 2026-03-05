@extends($layout_view)

@section('content')
  {!! $contentHtml !!}

  @php
    $recent = (isset($recentPosts) && is_iterable($recentPosts)) ? array_slice($recentPosts, 0, 3) : [];
  @endphp

  @if(!empty($recent))
    <section class="galaxy-recent">
      <h2>Latest Picks & Insights</h2>
      <div class="galaxy-cards">
        @foreach($recent as $p)
          <article class="galaxy-card">
            <h3><a href="{{ $p['href'] ?? '#' }}">{{ $p['title'] ?? 'Post' }}</a></h3>
            @if(!empty($p['desc']))<p>{{ $p['desc'] }}</p>@endif
            <a class="galaxy-readmore" href="{{ $p['href'] ?? '#' }}">Read more →</a>
          </article>
        @endforeach
      </div>
    </section>
  @endif
@endsection

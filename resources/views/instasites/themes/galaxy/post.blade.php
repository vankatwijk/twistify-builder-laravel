@extends($layout_view)

@section('content')
  <article class="galaxy-post">
    @if(!empty($title))
      <h1>{{ $title }}</h1>
    @endif

    @if(!empty($post['published_at']))
      <p class="galaxy-meta">
        {{ \Illuminate\Support\Carbon::parse($post['published_at'])->format('F j, Y') }}
        @if(!empty($post['author'])) · {{ $post['author'] }} @endif
      </p>
    @endif

    {!! $contentHtml !!}
  </article>
@endsection

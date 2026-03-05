@extends($layout_view)

@section('content')
  <article class="galaxy-post">
    @if(!empty($metaTitle))
      <h1>{{ $metaTitle }}</h1>
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

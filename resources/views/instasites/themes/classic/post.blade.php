@extends($layout_view)

@section('content')
  <article>
    <header class="post-header">
      @if(!empty($title))
        <h1 class="post-title">{{ $title }}</h1>
      @endif
      @if(!empty($post['published_at']))
        <div class="post-date">
          {{ \Illuminate\Support\Carbon::parse($post['published_at'])->format('F j, Y') }}
          @if(!empty($post['author']))
            · By {{ $post['author'] }}
          @endif
        </div>
      @endif
    </header>
    <div class="post-content">
      {!! $contentHtml !!}
    </div>
  </article>
@endsection

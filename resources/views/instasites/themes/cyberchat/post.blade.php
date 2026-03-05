@extends($layout_view)
@section('content')
  <article class="cyb-msg assistant">
    <div class="cyb-avatar">AI</div>
    <div class="cyb-msg-body prose">
      @if(!empty($title))
        <h1>{{ $title }}</h1>
      @endif
      @if(!empty($post['published_at']) || !empty($post['author']))
        <p class="cyb-post-meta">
          @if(!empty($post['published_at'])){{ \Illuminate\Support\Carbon::parse($post['published_at'])->format('F j, Y') }}@endif
          @if(!empty($post['author'])) · {{ $post['author'] }}@endif
        </p>
      @endif
      {!! $contentHtml !!}
    </div>
  </article>
@endsection

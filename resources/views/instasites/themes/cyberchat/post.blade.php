@extends($layout_view)

@section('content')
  <article class="cyb-post">
    @if(!empty($title))
      <h1 class="cyb-h1">{{ $title }}</h1>
    @endif
    {!! $contentHtml !!}
  </article>
@endsection

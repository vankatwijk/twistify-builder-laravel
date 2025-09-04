@extends($layout_view)

@section('content')
  <article class="blog-post">
    @if(!empty($title)) <h1>{{ $title }}</h1> @endif
    {!! $contentHtml !!}
  </article>
@endsection

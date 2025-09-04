@extends($layout_view)

@section('content')
  <article class="gen-post">
    @if(!empty($title)) <h1 class="gen-h1">{{ $title }}</h1> @endif
    {!! $contentHtml !!}
  </article>
@endsection

@extends($layout_view)
@section('content')
  <article class="cyb-msg assistant">
    <div class="cyb-avatar">AI</div>
    <div class="cyb-msg-body prose">
      {!! $contentHtml !!}
    </div>
  </article>
@endsection

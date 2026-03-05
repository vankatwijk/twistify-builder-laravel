@extends($layout_view)

@section('content')
  <section class="cyb-msg assistant">
    <div class="cyb-avatar">AI</div>
    <div class="cyb-msg-body">
      <h1 class="cyb-msg-title">{{ $title ?? 'Blog' }}</h1>

      @if(!empty($postsList))
        <div class="cyb-blog-list">
          @foreach($postsList as $p)
            <a class="cyb-blog-item" href="{{ $p['href'] ?? '#' }}">
              <h3>{{ $p['title'] ?? 'Post' }}</h3>
              @if(!empty($p['desc']))<p>{{ $p['desc'] }}</p>@endif
              @if(!empty($p['date']))<span>{{ \Illuminate\Support\Carbon::parse($p['date'])->toFormattedDateString() }}</span>@endif
            </a>
          @endforeach
        </div>
      @else
        <p class="cyb-muted">No posts yet.</p>
      @endif
    </div>
  </section>
@endsection
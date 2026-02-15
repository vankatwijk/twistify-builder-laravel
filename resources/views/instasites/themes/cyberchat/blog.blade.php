@extends($layout_view)

@section('content')
  <h1 class="gen-h1">{{ $title ?? 'Blog' }}</h1>
  @if(!empty($postsList))
    <section class="container gen-features" style="margin-top:12px">
      @foreach($postsList as $p)
        <a class="gen-feature" href="{{ $p['href'] ?? '#' }}">
          <h3 class="m-0">{{ $p['title'] ?? 'Post' }}</h3>
          @if(!empty($p['desc']))<p class="m-0 muted">{{ $p['desc'] }}</p>@endif
          @if(!empty($p['date']))<p class="m-0 muted" style="font-size:12px">{{ \Illuminate\Support\Carbon::parse($p['date'])->toFormattedDateString() }}</p>@endif
        </a>
      @endforeach
    </section>
  @else
    <p class="muted">No posts yet.</p>
  @endif
@endsection
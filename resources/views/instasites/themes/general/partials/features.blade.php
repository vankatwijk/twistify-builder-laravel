@php
  $features = $blueprint['theme']['features'] ?? null;  // array of strings
@endphp
@if(!empty($features) && is_array($features))
<section class="gen-features container">
  @foreach($features as $f)
    <div class="gen-feature">{{ $f }}</div>
  @endforeach
</section>
@endif

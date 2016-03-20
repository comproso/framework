<div class="page ui segment">
	<form method="post" action="/proceed/{{ $test_id }}">
		{{ csrf_field() }}
		@foreach($results as $result)
			{!! $result !!}
		@endforeach
		@if(!is_null($nav))
			@include($nav)
		@endif
	</form>
</div>
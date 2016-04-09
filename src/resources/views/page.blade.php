
<div class="page ui segment">
	<form class="cpage" method="post" action="{{ url('proceed') }}">
		<input type="hidden" name="cctrl_prvs" value="0">
		<input type="hidden" name="ccfg_tlmt" value="{{ $time_limit }}">
		<input type="hidden" name="ccfg_tvl" value="{{ $interval }}">
		<input type="hidden" name="ccusr_rnd" value="{{ $round }}">
		<input type="hidden" name="ccusr_tstrt" value="">
		<input type="hidden" name="ccusr_nd" value="">
		<input type="hidden" name="ccusr_rt" value="0">
		<input type="hidden" name="ccusr_ctns" value='{{ json_encode([]) }}'>
		@foreach($results as $result)
			{!! $result !!}
		@endforeach
	</form>
</div>
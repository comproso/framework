<!DOCTYPE html>
<html>
    <head>
        <title>comproso</title>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="{{ asset('assets/semantic-ui/semantic.min.css') }}" type="text/css" rel="stylesheet">
		<script src="{{ asset('assets/jquery/jquery.min.js') }}" type="text/javascript"></script>
		<script src="{{ asset('assets/semantic-ui/semantic.min.js') }}" type="text/javascript"></script>
        <?php if(isset($assets)):
	        	foreach($assets as $asset):
	        		if(preg_match("/(\.js)$/i", $asset)): ?>
			<script src="{{ asset($asset) }}" type="text/javascript"></script>
				<?php elseif(preg_match("/(\.css)$/i", $asset)): ?>
	        <link href="{{ asset($asset) }}" type="text/css" rel="stylesheet">
        <?php 		endif;
	        	endforeach;
	        endif; ?>
    </head>
    <body id="comproso">
		<div class="page ui segment">
			<form class="cpage" method="post" action="{{ url('proceed') }}">
				{{ csrf_field() }}
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
	</body>
</html>

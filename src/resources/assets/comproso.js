var version = "0.4.6";

function sendRequest(requestDataType)
{
	// set default
	if(requestDataType === undefined)
	{
		requestDataType = "json";
	}

	// in case of HTML
	if(requestDataType == "html")
	{
		$('form.cpage').submit();
	}

	if(requestDataType === "json")
	{
		var noredirection = true;

		// define data type
		var request = $.ajax($('form.cpage').attr('action'), {
			dataType: requestDataType,
			data: $('form.cpage input, form.cpage select, form.cpage textarea').serialize(),
			cache: false,
			timeout: 30000,
			method: "post",
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			beforeSend: function () {
				// set end time
				var ccusrnd = Date.now();

				$('form.cpage input[name="ccusr_nd"]').val(ccusrnd);
			},
			success: function (response) {
				// check for redirection request by server
				if(response.redirect === undefined)
				{
					noredirection = true;

					// update comproso items
					$('form.cpage input[name="ccfg_tmlt"]').val(response.time_limit);
					$('form.cpage input[name="ccfg_tvl"]').val(response.interval);
					$('form.cpage input[name="ccusr_rnd"]').val(response.round);
					$('form.cpage input[name="ccusr_ctns"]').val(JSON.stringify([]));

					// update items
					$.each(response.results, function (name, result) {
						if(result.results.accessors !== undefined)
						{
							// define element
							element = $('body#comproso form.cpage *[name="' + result.name + '"]');

							// replace attributes
							$.each(result.results.accessors, function (index, accessor) {

								// store history
								if(element.attr("data-" + accessor) !== undefined)
								{
									arr = element.data(accessor);
									arr.push(result.results[accessor]);
									element.data(accessor, arr);
								}

								element.attr(accessor, result.results[accessor]);
							});

							element.triggerHandler("updated");
						}
					});

					$(document).triggerHandler('jsonResponse');
				}
				else
				{
					noredirection = false;

					// reset data
					if(typeof tInt !== 'undefined')
					{
						clearTimeout(tInt);
					}

					sendRequest("html");
				}

				$(document).triggerHandler('ajaxProceeded');
			},
			complete: function () {
				if(noredirection)
				{
					// enable
					$('.cnav .button').prop('disabled', false).removeClass('disabled');

					// set start time
					$('form.cpage input[name="ccusr_tstrt"]').val(Date.now());
				}
			},
			done: function () {
				// update transaction time
				$('form.cpage input[name="ccusr_rt"]').val((Date.now() - ccusrnd));
			},
			error: function (xhr, status, errorThrown) {
				console.log( "Error: " + errorThrown );
				console.log( "Status: " + status );
				console.dir( xhr );
				document.write(xhr.responseText);
			}
		});
	}
}

// store process data
function storeProcessData(element) {
	if((element === undefined) && (!$(this).attr('name')))
		return;

	actions = JSON.parse($('form.cpage input[name="ccusr_ctns"]').val());

	// store new actions
	newactions = {};

	if(element === undefined)
	{
		newactions.item = $(this).attr('name');
		newactions.value = $(this).attr('value');
	}
	else
	{
		newactions.item = "reset";
		newactions.value = true;
	}

	newactions.tstmp = Date.now() - $('form.cpage input[name="ccusr_tstrt"]').val() ;

	actions.push(newactions);

	$('form.cpage input[name="ccusr_ctns"]').val(JSON.stringify(actions));
}

$(document).on("jsonResponse", function () {
	// auto send mode
	if($('form.cpage .cnav').length === 0)
	{
		// prt
		if($('form.cpage input[name="ccfg_tvl"]').val() > 0)
		{
			var tvlId = setTimeout(sendRequest, ($('form.cpage input[name="ccfg_tvl"]').val() * 1000));
		}
	}
});

$(document).on("ready", function () {
	// set beginning time
	$('form.cpage input[name="ccusr_tstrt"]').val(Date.now());

	// reset data
	if(typeof tInt !== 'undefined')
	{
		clearTimeout(tInt);
	}

	// pseudorealtime
	if(($('form.cpage .cnav').length === 0) && ($('form.cpage input[name="ccfg_tvl"]').val() > 0))
	{
		// timeout
		if($('form.cpage input[name="ccfg_tvl"]').val() > 5)
		{
			// count down
			var tInt = setTimeout(sendRequest, ($('form.cpage input[name="ccfg_tvl"]').val() * 1000));
		}
		else
		{
			var tInt = setTimeout(sendRequest, 5000);
		}
	}

	// navigation
	$('.cnav .button:not(.inactive):not(.disabled)').click(function () {

		// check if reset
		if($(this).hasClass('rst'))
		{
			$('form.cpage').trigger('reset');
		}
		else
		{
			// disable
			$('.cnav .button').prop('disabled', true).addClass('disabled');


			// check request
			if((typeof request === 'undefined') || (request.readyState === 0) || (request.readyState > 4))
			{
				// set direction
				if($(this).hasClass('bwd'))
				{
					$('form.cpage input[name="cctrl_prvs"]').val(1);
				}
				else
				{
					$('form.cpage input[name="cctrl_prvs"]').val(0);

				}

				// set end time
				$('form.cpage input[name="ccusr_nd"]').val(Date.now());

				// send form
				sendRequest();
			}
		}
	});

	// record process data on form fields
	$('form.cpage *:not(.notrace) input[name^="item"]:not(.notrace)').change(storeProcessData());
});
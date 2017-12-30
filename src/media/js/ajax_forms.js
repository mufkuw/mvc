$(function () {
	$('FORM[submit-type="ajax"]').submit(function (e) {
		$submit_button = $(this).find('[type="submit"]');
		if ($submit_button.hasClass('disabled'))
			return false;
		$submit_button.addClass('disabled');

		$html = $submit_button.html().replace("<i class=\"fa fa-spinner fa-spin\"></i> ", "");
		$submit_button.html("<i class=\"fa fa-spinner fa-spin\"></i> " + $html);
		$referer = $(this).attr('referer');
		$(this).attr('status', 1);
		$.ajax({
			url: $(this).attr('action'),
			data: $(this).serialize(),
			type: $(this).attr('method'),
			dataType: 'json',
			success: function (d, s, x) {
				$submit_button.html($html);
				$submit_button.removeClass('disabled');
				alerts = $.parseJSON(x.getResponseHeader('Alerts'));
				page_alerts(alerts);

				if ((alerts.length > 0 && alerts[0]['type'] === 'success'))
					$(location).attr('href', $referer);
			},
			error: function (d, s, e) {
				$submit_button.removeClass('disabled');
				console.log(s);
			}
		});
		return false;
	});
});
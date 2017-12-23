$(function () {

	$('FORM[submit-type="ajax"]').submit(function (e) {
		$referer = $(this).attr('referer');
		$.ajax({
			url: $(this).attr('action'),
			data: $('FORM.form-product').serialize(),
			type: $(this).attr('method'),
			dataType: 'json',
			success: function (d, s, x) {
				alerts = $.parseJSON(x.getResponseHeader('Alerts'));
				page_alerts(alerts);
				if ((alerts.length > 0 && alerts[0]['type'] === 'success'))
					$(location).attr('href', $referer);
			}
		});
		return false;
	});

});
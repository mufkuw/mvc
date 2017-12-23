$(function () {

	$('FORM[submit-type="ajax"]').submit(function (e) {
		$.ajax({
			url: $(this).attr('action'),
			data: $('FORM.form-product').serialize(),
			type: 'post',
			dataType: 'json',
			success: function (d, s, x) {
				errors = $.parseJSON(x.getResponseHeader('Alerts'));
				console.log(errors);
				if ((errors.length > 0 && errors[0]['type'] === 'success'))
					$(location).attr('href', $('#referer').val());
				page_alerts(errors);
			}
		});
		return false;
	});

});
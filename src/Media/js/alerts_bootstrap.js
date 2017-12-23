/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function page_alerts($alerts, $selector)
{
	if (!$selector)
		$selector = '#page_alerts';
	var alerts = '';
	if ($alerts !== undefined) {
		for (var i = 0; i < $alerts.length; i++) {
			alerts += $('#alert_template')
					.html();
			alerts = alerts.replace(/\[MESSAGE\]/g, $alerts[i]['message']);
			alerts = alerts.replace(/\[TYPE\]/g, $alerts[i]['type']);
			alerts = alerts.replace(/\[TITLE\]/g, $alerts[i]['title']);
		}
	}

	if (alerts !== '')
	{
		$($selector + ' .alert').slideUp('1500', 'swing', function () {
		});

		html = $.parseHTML(alerts);
		$(html).appendTo($selector).hide();
		//.css({ opacity: 0 });
		$(html).slideDown('100', 'swing', function () {
			$($selector + ' .alert').addClass('pop');
		});
		$($selector).html(html, 500);

	}

	$($selector + ' .alert').addClass('pop');

	$('html, body').animate({
		//scrollTop: $($selector).offset().top - 150
	}, 500);

}

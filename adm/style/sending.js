jQuery(function($)
{
	// Preload sending animation for silly IE and Chrome
	var $preloader = $('<button class="sending" style="position: absolute; top: -99px; left: -99px;"></button>').appendTo(document.body);
	setTimeout(function(){$preloader.remove();}, 10);

	// Forms submitting indication
	$('form input[type=submit]').on('click', function()
	{
		var $submit = $(this);
		if ($submit.hasClass('sending')) return false;
		$(this).parents('form').off('submit.sending').one('submit.sending', function(e)
		{
			if (e.isDefaultPrevented()) return;
			$submit.addClass('sending');
			var last = (new Date()).getTime();
			var timer = setInterval(function()
			{
				if ((new Date()).getTime() - last > 2000)
				{
					$submit.removeClass('sending');
					clearInterval(timer);
					return;
				}
				last = (new Date()).getTime();
			}, 1000);
		});
	});

	// Ctrl+Enter and Alt+Enter titles for default and alternate submit buttons
	$('form input[type=submit].default-submit-action').attr('title', 'Ctrl+Enter');
	$('form input[type=submit].alternate-submit-action').attr('title', 'Alt+Enter');

	// Enter, Ctrl+Enter and Alt+Enter handler
	$('form input[type=text], form input[type=password], form textarea').on('keydown', function (e)
	{
		var is_input = !$(this).is('textarea');

		// Detect enter in autocomplete
		if (is_input)
		{
			var in_autocomplete = $(this).data('in_autocomplete');
			$(this).data('in_autocomplete', (e.which == 40 /*down*/ || e.which == 38 /*up*/ || e.which == 34 /*pgdn*/ || e.which == 33 /*pgup*/));
			if (in_autocomplete && (e.which == 13 || e.which == 10)) return true;
		}

		if ((e.which == 13 || e.which == 10) && (is_input || e.ctrlKey || e.altKey))
		{
			// Find proper submit button
			var $form = $(this).parents('form');
			var $submit = $form.find('input[type=submit].' + (e.altKey ? 'alternate' : 'default') + '-submit-action:eq(0)');
			if ($submit.length == 0)
			{
				if (e.altKey) return false;
				$submit = $form.find('input[type=submit]');
				if ($submit.length == 0) return false;
				if ($submit.length > 1)
				{
					$submit = $form.find('input[type=submit][name=submit]');
					if ($submit.length != 1) return false;
				}
			}

			// Submit form
			$submit.click();
			return false;
		}

		return true;
	});
});

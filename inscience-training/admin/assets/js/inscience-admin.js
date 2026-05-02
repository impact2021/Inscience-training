/* global jQuery */
(function ($) {
	'use strict';

	// Toggle city field based on course type
	$(document).on('change', '#course_type', function () {
		var val = $(this).val();
		$('#inscience-city-wrap').toggle(val === 'classroom');
	});

	// Shortcode copy buttons
	$(document).on('click', '.inscience-copy-btn', function () {
		var $btn = $(this);
		var text = $btn.data('clipboard');
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(function () {
				flashCopied($btn);
			});
		} else {
			// Fallback for older browsers
			var $temp = $('<input>').val(text).appendTo('body').select();
			document.execCommand('copy');
			$temp.remove();
			flashCopied($btn);
		}
	});

	function flashCopied($btn) {
		var original = $btn.text();
		$btn.text('Copied!').addClass('copied');
		setTimeout(function () {
			$btn.text(original).removeClass('copied');
		}, 1800);
	}

})(jQuery);

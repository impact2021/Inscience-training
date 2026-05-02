/* global jQuery */
(function ($) {
	'use strict';

	// Toggle city field based on course type
	$(document).on('change', '#course_type', function () {
		var val = $(this).val();
		$('#inscience-city-wrap').toggle(val === 'classroom');
	});

})(jQuery);

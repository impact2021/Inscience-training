/* global jQuery, inscienceEnrolment */
(function ($) {
	'use strict';

	var form     = $('#inscience-enrolment-form');
	var submitBtn = $('#inscience-submit-btn');
	var errorBox  = $('#inscience-form-error');

	if (!form.length) return;

	// Show/hide Zoom declaration paragraph
	$(document).on('change', '#course_id', function () {
		var type = $('option:selected', this).data('type');
		$('#inscience-zoom-declaration').toggle(type === 'zoom');
	});

	// Collect ethnic group checkboxes into hidden field
	$(document).on('change', '[name="ethnic_group[]"]', function () {
		var selected = [];
		$('[name="ethnic_group[]"]:checked').each(function () {
			selected.push($(this).val());
		});
		$('#ethnic_group_hidden').val(selected.join(', '));
	});

	form.on('submit', function (e) {
		e.preventDefault();

		// Validate ethnic group (at least one must be checked)
		if (!$('[name="ethnic_group[]"]:checked').length) {
			showError(inscienceL10n ? inscienceL10n.ethnicRequired : 'Please select at least one ethnic group.');
			return;
		}

		var ethnicVal = $('[name="ethnic_group[]"]:checked').map(function () {
			return $(this).val();
		}).get().join(', ');

		var formData = form.serializeArray();

		// Replace ethnic_group placeholder with real value
		formData = formData.filter(function (f) {
			return f.name !== 'ethnic_group';
		});
		formData.push({ name: 'ethnic_group', value: ethnicVal });

		formData.push({ name: 'action', value: 'inscience_submit_enrolment' });
		formData.push({ name: 'nonce',  value: form.find('[name="inscience_enrolment_nonce"]').val() });

		setLoading(true);
		errorBox.hide();

		$.post(
			inscienceEnrolment.ajaxurl,
			$.param(formData),
			function (response) {
				if (response.success) {
					handleSuccess(response.data);
				} else {
					showError(response.data ? response.data.message : 'An error occurred.');
					setLoading(false);
				}
			}
		).fail(function () {
			showError('A network error occurred. Please try again.');
			setLoading(false);
		});
	});

	function handleSuccess(data) {
		if (data.payment_method === 'stripe' && data.stripe_checkout_url) {
			window.location.href = data.stripe_checkout_url;
		} else {
			// Redirect with success status
			var currentUrl = window.location.href.split('?')[0];
			window.location.href = currentUrl + '?inscience_status=success&inscience_enrolment_id=' + data.enrolment_id;
		}
	}

	function showError(msg) {
		errorBox.text(msg).show();
		$('html, body').animate({ scrollTop: errorBox.offset().top - 80 }, 400);
	}

	function setLoading(loading) {
		if (loading) {
			submitBtn.prop('disabled', true);
			submitBtn.find('.inscience-btn-text').hide();
			submitBtn.find('.inscience-btn-loading').show();
		} else {
			submitBtn.prop('disabled', false);
			submitBtn.find('.inscience-btn-text').show();
			submitBtn.find('.inscience-btn-loading').hide();
		}
	}

})(jQuery);

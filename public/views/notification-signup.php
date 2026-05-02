<?php
/**
 * Public notification signup form view.
 *
 * @package InScience_Training
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="inscience-notify-wrap">
	<form id="inscience-notify-form" class="inscience-form">
		<?php wp_nonce_field( 'inscience_notify', 'inscience_notify_nonce', true, true ); ?>

		<div class="inscience-field inscience-field-required">
			<label for="notify_name"><?php esc_html_e( 'Your name', 'inscience-training' ); ?></label>
			<input type="text" id="notify_name" name="name" class="inscience-input">
		</div>

		<div class="inscience-field inscience-field-required">
			<label for="notify_email"><?php esc_html_e( 'Email address', 'inscience-training' ); ?> <span class="required">*</span></label>
			<input type="email" id="notify_email" name="email" required class="inscience-input">
		</div>

		<div class="inscience-field">
			<label for="notify_course_type"><?php esc_html_e( 'I am interested in…', 'inscience-training' ); ?></label>
			<select id="notify_course_type" name="course_type" class="inscience-input">
				<option value=""><?php esc_html_e( 'All courses', 'inscience-training' ); ?></option>
				<?php foreach ( InScience_Course_CPT::TYPES as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?> <?php esc_html_e( 'courses', 'inscience-training' ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="inscience-form-submit">
			<div id="inscience-notify-error" class="inscience-notice inscience-error" style="display:none"></div>
			<button type="submit" id="inscience-notify-submit" class="inscience-btn inscience-btn-submit">
				<span class="inscience-btn-text"><?php esc_html_e( 'Notify Me', 'inscience-training' ); ?></span>
				<span class="inscience-btn-loading" style="display:none"><?php esc_html_e( 'Subscribing…', 'inscience-training' ); ?></span>
			</button>
		</div>
	</form>
</div>

<script>
(function($){
	$('#inscience-notify-form').on('submit', function(e){
		e.preventDefault();
		var $btn = $('#inscience-notify-submit');
		$btn.find('.inscience-btn-text').hide();
		$btn.find('.inscience-btn-loading').show();
		$btn.prop('disabled', true);
		$('#inscience-notify-error').hide();

		var data = $(this).serializeArray();
		data.push({ name: 'action', value: 'inscience_notify_signup' });
		data.push({ name: 'nonce', value: $(this).find('[name="inscience_notify_nonce"]').val() });

		$.post(
			'<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
			$.param(data),
			function(response){
				if (response.success) {
					$('#inscience-notify-form').html(
						'<div class="inscience-notice inscience-success">' +
						'<?php echo esc_js( __( 'You have been subscribed to new course notifications!', 'inscience-training' ) ); ?>' +
						'</div>'
					);
				} else {
					$('#inscience-notify-error').text(response.data.message).show();
					$btn.find('.inscience-btn-text').show();
					$btn.find('.inscience-btn-loading').hide();
					$btn.prop('disabled', false);
				}
			}
		).fail(function(){
			$('#inscience-notify-error').text('<?php echo esc_js( __( 'An error occurred. Please try again.', 'inscience-training' ) ); ?>').show();
			$btn.find('.inscience-btn-text').show();
			$btn.find('.inscience-btn-loading').hide();
			$btn.prop('disabled', false);
		});
	});
})(jQuery);
</script>

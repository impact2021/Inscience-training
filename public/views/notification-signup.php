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

		<div class="inscience-form-section">
			<div class="inscience-field-row">
				<div class="inscience-field">
					<label for="notify_name"><?php esc_html_e( 'Your name', 'inscience-training' ); ?></label>
					<input type="text" id="notify_name" name="name" class="inscience-input">
				</div>

				<div class="inscience-field">
					<label for="notify_email"><?php esc_html_e( 'Email address', 'inscience-training' ); ?> <span class="required">*</span></label>
					<input type="email" id="notify_email" name="email" required class="inscience-input">
				</div>
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
		</div>
	</form>
</div>

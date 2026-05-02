<?php
/**
 * Settings admin view.
 *
 * @package InScience_Training
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="wrap inscience-admin-wrap">
	<h1 class="inscience-page-title">
		<span class="dashicons dashicons-admin-generic"></span>
		<?php esc_html_e( 'Settings', 'inscience-training' ); ?>
	</h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'inscience_save_settings_action', 'inscience_nonce' ); ?>
		<input type="hidden" name="action" value="inscience_save_settings">

		<div class="inscience-form-grid">
			<div class="inscience-form-main">

				<!-- Stripe Settings -->
				<div class="inscience-card">
					<h2><?php esc_html_e( '💳 Stripe Payment Settings', 'inscience-training' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Enter your Stripe API keys to accept credit card payments. Keys are stored in the WordPress database (not version-controlled).', 'inscience-training' ); ?></p>
					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Publishable Key', 'inscience-training' ); ?></th>
							<td><input type="text" name="inscience_stripe_public_key" class="regular-text" value="<?php echo esc_attr( get_option( 'inscience_stripe_public_key', '' ) ); ?>" placeholder="pk_live_..."></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Secret Key', 'inscience-training' ); ?></th>
							<td><input type="password" name="inscience_stripe_secret_key" class="regular-text" value="<?php echo esc_attr( get_option( 'inscience_stripe_secret_key', '' ) ); ?>" placeholder="sk_live_..."></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Webhook Secret', 'inscience-training' ); ?></th>
							<td>
								<input type="password" name="inscience_stripe_webhook_secret" class="regular-text" value="<?php echo esc_attr( get_option( 'inscience_stripe_webhook_secret', '' ) ); ?>" placeholder="whsec_...">
								<p class="description">
									<?php
									printf(
										/* translators: %s: webhook URL */
										esc_html__( 'Configure this Stripe webhook endpoint: %s', 'inscience-training' ),
										'<code>' . esc_html( admin_url( 'admin-ajax.php?action=inscience_stripe_webhook' ) ) . '</code>'
									);
									?>
								</p>
							</td>
						</tr>
					</table>
				</div>

				<!-- Bank Transfer Settings -->
				<div class="inscience-card">
					<h2><?php esc_html_e( '🏦 Bank Transfer Details', 'inscience-training' ); ?></h2>
					<p class="description"><?php esc_html_e( 'These details are included in enrolment confirmation emails when the attendee selects bank transfer as their payment method.', 'inscience-training' ); ?></p>
					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Bank Name', 'inscience-training' ); ?></th>
							<td><input type="text" name="inscience_bank_name" class="regular-text" value="<?php echo esc_attr( get_option( 'inscience_bank_name', '' ) ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Account Name', 'inscience-training' ); ?></th>
							<td><input type="text" name="inscience_bank_account_name" class="regular-text" value="<?php echo esc_attr( get_option( 'inscience_bank_account_name', '' ) ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Account Number', 'inscience-training' ); ?></th>
							<td><input type="text" name="inscience_bank_account_number" class="regular-text" value="<?php echo esc_attr( get_option( 'inscience_bank_account_number', '' ) ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Reference Instructions', 'inscience-training' ); ?></th>
							<td><textarea name="inscience_bank_reference_instructions" class="large-text" rows="2"><?php echo esc_textarea( get_option( 'inscience_bank_reference_instructions', 'Please use your enrolment ID as the payment reference.' ) ); ?></textarea></td>
						</tr>
					</table>
				</div>
			</div>

			<div class="inscience-form-sidebar">
				<div class="inscience-card">
					<h2><?php esc_html_e( '📧 Email Settings', 'inscience-training' ); ?></h2>
					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'From Name', 'inscience-training' ); ?></th>
							<td><input type="text" name="inscience_email_from_name" class="regular-text" value="<?php echo esc_attr( get_option( 'inscience_email_from_name', get_bloginfo( 'name' ) ) ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'From Address', 'inscience-training' ); ?></th>
							<td><input type="email" name="inscience_email_from_address" class="regular-text" value="<?php echo esc_attr( get_option( 'inscience_email_from_address', get_option( 'admin_email' ) ) ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Admin Notification Email', 'inscience-training' ); ?></th>
							<td><input type="email" name="inscience_admin_email" class="regular-text" value="<?php echo esc_attr( get_option( 'inscience_admin_email', get_option( 'admin_email' ) ) ); ?>"></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Email Logo URL', 'inscience-training' ); ?></th>
							<td><input type="url" name="inscience_email_logo" class="regular-text" value="<?php echo esc_attr( get_option( 'inscience_email_logo', '' ) ); ?>"></td>
						</tr>
					</table>
				</div>

				<div class="inscience-card">
					<h2><?php esc_html_e( '📄 Pages', 'inscience-training' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Set the page that contains the [inscience_enrolment_form] shortcode. This page is used for payment redirect links.', 'inscience-training' ); ?></p>
					<p>
						<label><strong><?php esc_html_e( 'Enrolment Form Page', 'inscience-training' ); ?></strong></label>
						<?php
						wp_dropdown_pages( array(
							'name'              => 'inscience_enrolment_page_id',
							'selected'          => get_option( 'inscience_enrolment_page_id', 0 ),
							'show_option_none'  => __( '— Select —', 'inscience-training' ),
							'option_none_value' => 0,
						) );
						?>
					</p>
				</div>

				<div class="inscience-card">
					<p>
						<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save Settings', 'inscience-training' ); ?></button>
					</p>
				</div>
			</div>
		</div>
	</form>
</div>

<?php
/**
 * Settings admin view.
 *
 * @package InScience_Training
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings';
$base_url   = admin_url( 'admin.php?page=inscience-settings' );
?>
<div class="wrap inscience-admin-wrap">
	<h1 class="inscience-page-title">
		<span class="dashicons dashicons-admin-generic"></span>
		<?php esc_html_e( 'Settings', 'inscience-training' ); ?>
	</h1>

	<!-- Tab navigation -->
	<nav class="nav-tab-wrapper inscience-tab-nav">
		<a href="<?php echo esc_url( $base_url . '&tab=settings' ); ?>"
			class="nav-tab<?php echo 'settings' === $active_tab ? ' nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-admin-generic"></span>
			<?php esc_html_e( 'Settings', 'inscience-training' ); ?>
		</a>
		<a href="<?php echo esc_url( $base_url . '&tab=shortcodes' ); ?>"
			class="nav-tab<?php echo 'shortcodes' === $active_tab ? ' nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-shortcode"></span>
			<?php esc_html_e( 'Shortcodes', 'inscience-training' ); ?>
		</a>
	</nav>

	<?php if ( 'shortcodes' === $active_tab ) : ?>

	<!-- =====================================================================
	     SHORTCODES TAB
	     ===================================================================== -->
	<div class="inscience-tab-content">
		<div class="inscience-card">
			<h2><?php esc_html_e( 'Available Shortcodes', 'inscience-training' ); ?></h2>
			<p><?php esc_html_e( 'Add any of these shortcodes to any page or post to display the corresponding feature on the front end of your site.', 'inscience-training' ); ?></p>
		</div>

		<?php
		$shortcodes = array(
			array(
				'icon'        => '🗓',
				'tag'         => '[inscience_calendar]',
				'title'       => __( 'Course Calendar', 'inscience-training' ),
				'purpose'     => __( 'Displays a full interactive calendar (month view and list view) of all upcoming, published courses. Each event is colour-coded by delivery type — navy for Classroom courses and green for Zoom courses.', 'inscience-training' ),
				'attributes'  => array(),
				'behaviour'   => array(
					__( 'Clicking any event opens a detail modal showing the course title, delivery type, dates, time, location/city, NZQA unit standard codes, price, and enrolment status.', 'inscience-training' ),
					__( 'The modal includes an <strong>Enrol Now</strong> button that links directly to the enrolment form page with the course pre-selected.', 'inscience-training' ),
					__( 'Courses marked as <em>Full</em> show "Course Full" instead of the Enrol Now button.', 'inscience-training' ),
					__( 'Cancelled courses are hidden from the calendar automatically.', 'inscience-training' ),
				),
				'requires'    => __( 'No additional attributes required.', 'inscience-training' ),
				'tip'         => __( 'Tip: Set the <strong>Enrolment Form Page</strong> and the <strong>Notification Sign-up Page</strong> in the Settings tab so the Enrol Now button and the floating notification widget link to the correct pages.', 'inscience-training' ),
			),
			array(
				'icon'        => '📝',
				'tag'         => '[inscience_enrolment_form]',
				'title'       => __( 'Course Enrolment Form', 'inscience-training' ),
				'purpose'     => __( 'Renders the full NZQA-compliant enrolment form. Visitors can select a course, enter their personal details, choose a payment method (Stripe, bank transfer, or on account), accept the declaration, and submit their enrolment.', 'inscience-training' ),
				'attributes'  => array(
					array(
						'name'     => 'course_id',
						'type'     => 'integer',
						'default'  => '0 (shows dropdown of all upcoming courses)',
						'example'  => '[inscience_enrolment_form course_id="42"]',
						'desc'     => __( 'Pre-selects the specified course in the dropdown, useful when linking from the calendar modal or a course-specific page.', 'inscience-training' ),
					),
				),
				'behaviour'   => array(
					__( 'Displays a dropdown of all upcoming, open courses. Cancelled and full courses are excluded or marked appropriately.', 'inscience-training' ),
					__( 'On successful submission the attendee is redirected back to the same page with a confirmation message.', 'inscience-training' ),
					__( 'If the attendee chooses <strong>Stripe</strong>, they are redirected to a Stripe Checkout page and returned here after payment.', 'inscience-training' ),
					__( 'An enrolment confirmation email is sent to the attendee and an admin notification email is sent to the address configured in Settings.', 'inscience-training' ),
					__( 'The Zoom-specific declaration clause is shown automatically when a Zoom course is selected.', 'inscience-training' ),
				),
				'requires'    => __( 'Set the <strong>Enrolment Form Page</strong> in Settings → Pages so that Stripe redirect URLs are correct.', 'inscience-training' ),
				'tip'         => __( 'Tip: Place this shortcode on a dedicated page (e.g. <em>Enrol</em>) and set that page in Settings.', 'inscience-training' ),
			),
			array(
				'icon'        => '🔔',
				'tag'         => '[inscience_notification_signup]',
				'title'       => __( 'New Course Notification Sign-up', 'inscience-training' ),
				'purpose'     => __( 'Shows a short sign-up form where visitors can subscribe to receive an email whenever a new course is published on the site. Subscribers can optionally specify a delivery-type preference (all courses, classroom only, or Zoom only).', 'inscience-training' ),
				'attributes'  => array(),
				'behaviour'   => array(
					__( 'On submission, the email address is saved to the notifications database table and a success message is shown without a page reload.', 'inscience-training' ),
					__( 'If the email address is already subscribed, an informational message is shown instead.', 'inscience-training' ),
					__( 'When a course is published, an automated email is sent to all matching subscribers using the <em>New Course Notification</em> email template (editable under the Emails menu).', 'inscience-training' ),
					__( 'Every notification email includes a personalised unsubscribe link. Clicking it removes the subscriber instantly.', 'inscience-training' ),
				),
				'requires'    => __( 'No additional attributes required.', 'inscience-training' ),
				'tip'         => __( 'Tip: Place this shortcode in a sidebar widget area, a footer section, or on a dedicated "Stay Updated" page.', 'inscience-training' ),
			),
		);
		?>

		<?php foreach ( $shortcodes as $sc ) : ?>
		<div class="inscience-card inscience-shortcode-card">
			<h2><?php echo esc_html( $sc['icon'] . ' ' . $sc['title'] ); ?></h2>

			<div class="inscience-shortcode-tag-wrap">
				<code class="inscience-shortcode-tag"><?php echo esc_html( $sc['tag'] ); ?></code>
				<button type="button" class="button button-small inscience-copy-btn"
					data-clipboard="<?php echo esc_attr( $sc['tag'] ); ?>">
					<?php esc_html_e( 'Copy', 'inscience-training' ); ?>
				</button>
			</div>

			<p><?php echo esc_html( $sc['purpose'] ); ?></p>

			<?php if ( ! empty( $sc['attributes'] ) ) : ?>
			<h3><?php esc_html_e( 'Attributes', 'inscience-training' ); ?></h3>
			<table class="wp-list-table widefat inscience-table inscience-shortcode-attrs">
				<thead>
					<tr>
						<th style="width:160px"><?php esc_html_e( 'Attribute', 'inscience-training' ); ?></th>
						<th style="width:80px"><?php esc_html_e( 'Type', 'inscience-training' ); ?></th>
						<th style="width:220px"><?php esc_html_e( 'Default', 'inscience-training' ); ?></th>
						<th><?php esc_html_e( 'Description', 'inscience-training' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $sc['attributes'] as $attr ) : ?>
				<tr>
					<td><code><?php echo esc_html( $attr['name'] ); ?></code></td>
					<td><?php echo esc_html( $attr['type'] ); ?></td>
					<td><small><?php echo esc_html( $attr['default'] ); ?></small></td>
					<td>
						<?php echo esc_html( $attr['desc'] ); ?>
						<?php if ( ! empty( $attr['example'] ) ) : ?>
						<br><strong><?php esc_html_e( 'Example:', 'inscience-training' ); ?></strong>
						<code><?php echo esc_html( $attr['example'] ); ?></code>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php else : ?>
			<p class="inscience-shortcode-no-attrs">
				<span class="dashicons dashicons-yes-alt" style="color:#00a651"></span>
				<?php echo esc_html( $sc['requires'] ); ?>
			</p>
			<?php endif; ?>

			<?php if ( ! empty( $sc['behaviour'] ) ) : ?>
			<h3><?php esc_html_e( 'Behaviour', 'inscience-training' ); ?></h3>
			<ul class="inscience-shortcode-behaviour">
				<?php foreach ( $sc['behaviour'] as $point ) : ?>
				<li><?php echo wp_kses( $point, array( 'strong' => array(), 'em' => array() ) ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			<?php if ( ! empty( $sc['tip'] ) ) : ?>
			<div class="inscience-shortcode-tip">
				<span class="dashicons dashicons-lightbulb"></span>
				<?php echo wp_kses( $sc['tip'], array( 'strong' => array(), 'em' => array() ) ); ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>

	<?php else : ?>

	<!-- =====================================================================
	     SETTINGS TAB (existing form)
	     ===================================================================== -->
	<div class="inscience-tab-content">
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
					<p class="description"><?php esc_html_e( 'Set the pages used by the plugin for enrolments and notification sign-ups.', 'inscience-training' ); ?></p>
					<p>
						<label><strong><?php esc_html_e( 'Enrolment Form Page', 'inscience-training' ); ?></strong></label>
						<span class="description"><?php esc_html_e( 'The page containing [inscience_enrolment_form]. Used for the Enrol Now button and payment redirects.', 'inscience-training' ); ?></span>
						<?php
						wp_dropdown_pages( array(
							'name'              => 'inscience_enrolment_page_id',
							'selected'          => get_option( 'inscience_enrolment_page_id', 0 ),
							'show_option_none'  => __( '— Select —', 'inscience-training' ),
							'option_none_value' => 0,
						) );
						?>
					</p>
					<p>
						<label><strong><?php esc_html_e( 'Notification Sign-up Page', 'inscience-training' ); ?></strong></label>
						<span class="description"><?php esc_html_e( 'The page containing [inscience_notification_signup]. Linked from the floating "Get notified" widget on the calendar.', 'inscience-training' ); ?></span>
						<?php
						wp_dropdown_pages( array(
							'name'              => 'inscience_notification_page_id',
							'selected'          => get_option( 'inscience_notification_page_id', 0 ),
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
	</div><!-- /.inscience-tab-content -->
	<?php endif; ?>
</div><!-- /.wrap -->

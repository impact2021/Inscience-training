<?php
/**
 * Settings admin view.
 *
 * @package InScience_Training
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings';
$base_url   = admin_url( 'admin.php?page=inscience-settings' );

// Load saved course titles.
$saved_titles_raw = get_option( 'inscience_course_titles', '[]' );
$saved_titles     = json_decode( $saved_titles_raw, true );
if ( ! is_array( $saved_titles ) ) {
	$saved_titles = array();
}
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
		<a href="<?php echo esc_url( $base_url . '&tab=course-titles' ); ?>"
			class="nav-tab<?php echo 'course-titles' === $active_tab ? ' nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-list-view"></span>
			<?php esc_html_e( 'Course Titles', 'inscience-training' ); ?>
		</a>
		<a href="<?php echo esc_url( $base_url . '&tab=shortcodes' ); ?>"
			class="nav-tab<?php echo 'shortcodes' === $active_tab ? ' nav-tab-active' : ''; ?>">
			<span class="dashicons dashicons-shortcode"></span>
			<?php esc_html_e( 'Shortcodes', 'inscience-training' ); ?>
		</a>
	</nav>

	<?php if ( 'course-titles' === $active_tab ) : ?>

	<!-- =====================================================================
	     COURSE TITLES TAB
	     ===================================================================== -->
	<div class="inscience-tab-content">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'inscience_save_course_titles_action', 'inscience_nonce' ); ?>
			<input type="hidden" name="action" value="inscience_save_course_titles">

			<div class="inscience-card">
				<h2>
					<span class="dashicons dashicons-list-view"></span>
					<?php esc_html_e( 'Predefined Course Titles', 'inscience-training' ); ?>
				</h2>
				<p class="description">
					<?php esc_html_e( 'Enter one course title per line. These titles will appear as suggestions when adding a new course, and as filter options above the public calendar.', 'inscience-training' ); ?>
				</p>
				<table class="form-table">
					<tr>
						<th><label for="inscience_course_titles"><?php esc_html_e( 'Course Titles', 'inscience-training' ); ?></label></th>
						<td>
							<textarea id="inscience_course_titles" name="inscience_course_titles"
								class="large-text" rows="10"
								placeholder="<?php esc_attr_e( 'e.g. NZQA Oral fluid drug testing US32327 & 32328', 'inscience-training' ); ?>"
							><?php echo esc_textarea( implode( "\n", $saved_titles ) ); ?></textarea>
							<p class="description"><?php esc_html_e( 'One title per line. Blank lines are ignored. Duplicate titles are automatically removed.', 'inscience-training' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<p>
				<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save Course Titles', 'inscience-training' ); ?></button>
			</p>
		</form>
	</div>

	<?php elseif ( 'shortcodes' === $active_tab ) : ?>

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
				'icon'        => 'dashicons-calendar-alt',
				'tag'         => '[inscience_calendar]',
				'title'       => __( 'Course Calendar', 'inscience-training' ),
				'purpose'     => __( 'Displays a full interactive calendar (month view and list view) of all upcoming, published courses. Each event is colour-coded by delivery type — navy for Classroom courses and green for Zoom courses.', 'inscience-training' ),
				'attributes'  => array(
					array(
						'name'     => 'inline_form',
						'type'     => 'boolean',
						'default'  => '0 (enrolment form is on a separate page)',
						'example'  => '[inscience_calendar inline_form="1"]',
						'desc'     => __( 'When set to 1, the enrolment form is rendered directly beside the calendar (right column on desktop, below on mobile). Clicking Enrol Now in the event modal scrolls to the form and pre-selects the course â no separate page required.', 'inscience-training' ),
					),
				),
				'behaviour'   => array(
					__( 'Clicking any event opens a detail modal showing the course title, delivery type, dates, time, location/city, NZQA unit standard codes, price, and enrolment status.', 'inscience-training' ),
					__( 'The modal includes an <strong>Enrol Now</strong> button that links directly to the enrolment form page with the course pre-selected.', 'inscience-training' ),
					__( 'Predefined course titles (managed under Settings â Course Titles) appear as a filter dropdown above the calendar, letting visitors narrow courses by title and/or delivery type.', 'inscience-training' ),
					__( 'Courses marked as <em>Full</em> show "Course Full" instead of the Enrol Now button.', 'inscience-training' ),
					__( 'Cancelled courses are hidden from the calendar automatically.', 'inscience-training' ),
				),
				'requires'    => __( 'No additional attributes required.', 'inscience-training' ),
				'tip'         => __( 'Tip: Use <code>inline_form="1"</code> for an all-in-one page, or set the <strong>Enrolment Form Page</strong> in Settings for a separate enrolment page. Set the <strong>Notification Sign-up Page</strong> in Settings so the floating notification widget links to the correct page.', 'inscience-training' ),
			),
			array(
				'icon'        => 'dashicons-edit',
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
				'icon'        => 'dashicons-bell',
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
			<h2>
				<span class="dashicons <?php echo esc_attr( $sc['icon'] ); ?>"></span>
				<?php echo esc_html( $sc['title'] ); ?>
			</h2>

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
				<span class="dashicons dashicons-yes-alt"></span>
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

		<!-- Stripe Settings -->
		<div class="inscience-card">
			<h2>
				<span class="dashicons dashicons-money-alt"></span>
				<?php esc_html_e( 'Stripe Payment Settings', 'inscience-training' ); ?>
			</h2>
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
			<h2>
				<span class="dashicons dashicons-money"></span>
				<?php esc_html_e( 'Bank Transfer Details', 'inscience-training' ); ?>
			</h2>
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

		<!-- Email Settings -->
		<div class="inscience-card">
			<h2>
				<span class="dashicons dashicons-email-alt"></span>
				<?php esc_html_e( 'Email Settings', 'inscience-training' ); ?>
			</h2>
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

		<!-- Pages -->
		<div class="inscience-card">
			<h2>
				<span class="dashicons dashicons-admin-page"></span>
				<?php esc_html_e( 'Pages', 'inscience-training' ); ?>
			</h2>
			<p class="description"><?php esc_html_e( 'Set the pages used by the plugin for enrolments and notification sign-ups.', 'inscience-training' ); ?></p>
			<table class="form-table">
				<tr>
					<th>
						<?php esc_html_e( 'Enrolment Form Page', 'inscience-training' ); ?>
						<p class="description"><?php esc_html_e( 'The page containing [inscience_enrolment_form]. Used for the Enrol Now button and payment redirects.', 'inscience-training' ); ?></p>
					</th>
					<td>
						<?php
						wp_dropdown_pages( array(
							'name'              => 'inscience_enrolment_page_id',
							'selected'          => get_option( 'inscience_enrolment_page_id', 0 ),
							'show_option_none'  => __( '— Select —', 'inscience-training' ),
							'option_none_value' => 0,
						) );
						?>
					</td>
				</tr>
				<tr>
					<th>
						<?php esc_html_e( 'Notification Sign-up Page', 'inscience-training' ); ?>
						<p class="description"><?php esc_html_e( 'The page containing [inscience_notification_signup]. Linked from the floating "Get notified" widget on the calendar.', 'inscience-training' ); ?></p>
					</th>
					<td>
						<?php
						wp_dropdown_pages( array(
							'name'              => 'inscience_notification_page_id',
							'selected'          => get_option( 'inscience_notification_page_id', 0 ),
							'show_option_none'  => __( '— Select —', 'inscience-training' ),
							'option_none_value' => 0,
						) );
						?>
					</td>
				</tr>
			</table>
		</div>

		<p>
			<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save Settings', 'inscience-training' ); ?></button>
		</p>
	</form>
	</div><!-- /.inscience-tab-content -->
	<?php endif; ?>
</div><!-- /.wrap -->

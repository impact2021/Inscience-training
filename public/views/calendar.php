<?php
/**
 * Public calendar view.
 *
 * @package InScience_Training
 * Variables available: $inline_form (bool), $preset_course_id (int, only when $inline_form is true)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="inscience-calendar-wrap<?php echo $inline_form ? ' inscience-cal-with-form' : ''; ?>">
<div class="inscience-cal-main">
	<!-- Filters bar -->
	<div class="inscience-cal-filters">
		<!-- Type legend / filter -->
		<div class="inscience-calendar-legend">
			<span class="inscience-legend-item" data-filter="classroom" role="button" tabindex="0">
				<span class="inscience-legend-dot inscience-legend-classroom"></span><?php esc_html_e( 'Classroom', 'inscience-training' ); ?>
			</span>
			<span class="inscience-legend-item" data-filter="zoom" role="button" tabindex="0">
				<span class="inscience-legend-dot inscience-legend-zoom"></span><?php esc_html_e( 'Zoom (Online)', 'inscience-training' ); ?>
			</span>
		</div>

		<?php
		$filter_titles = isset( $predefined_titles ) ? $predefined_titles : array();
		if ( ! empty( $filter_titles ) ) :
		?>
		<!-- Course title filter -->
		<div class="inscience-title-filter-wrap">
			<label for="inscience-title-filter" class="screen-reader-text"><?php esc_html_e( 'Filter by course title', 'inscience-training' ); ?></label>
			<select id="inscience-title-filter" class="inscience-title-filter">
				<option value=""><?php esc_html_e( '— All course titles —', 'inscience-training' ); ?></option>
				<?php foreach ( $filter_titles as $ft ) : ?>
				<option value="<?php echo esc_attr( $ft ); ?>"><?php echo esc_html( $ft ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php endif; ?>
	</div>

	<!-- FullCalendar container -->
	<div id="inscience-fullcalendar"></div>

	<!-- Event detail modal -->
	<div id="inscience-modal-overlay" class="inscience-modal-overlay" style="display:none" role="dialog" aria-modal="true" aria-labelledby="inscience-modal-title">
		<div class="inscience-modal">
			<button class="inscience-modal-close" aria-label="<?php esc_attr_e( 'Close', 'inscience-training' ); ?>">&times;</button>
			<h3 id="inscience-modal-title" class="inscience-modal-title"></h3>
			<table class="inscience-modal-table">
				<tr class="inscience-modal-row-type">
					<th><?php esc_html_e( 'Type', 'inscience-training' ); ?></th>
					<td class="inscience-modal-type"></td>
				</tr>
				<tr class="inscience-modal-row-date">
					<th><?php esc_html_e( 'Date', 'inscience-training' ); ?></th>
					<td class="inscience-modal-date"></td>
				</tr>
				<tr class="inscience-modal-row-time">
					<th><?php esc_html_e( 'Time', 'inscience-training' ); ?></th>
					<td class="inscience-modal-time"></td>
				</tr>
				<tr class="inscience-modal-row-location">
					<th><?php esc_html_e( 'Location', 'inscience-training' ); ?></th>
					<td class="inscience-modal-location"></td>
				</tr>
				<tr class="inscience-modal-row-us">
					<th><?php esc_html_e( 'Unit Standards', 'inscience-training' ); ?></th>
					<td class="inscience-modal-us"></td>
				</tr>
				<tr class="inscience-modal-row-price">
					<th><?php esc_html_e( 'Price', 'inscience-training' ); ?></th>
					<td class="inscience-modal-price"></td>
				</tr>
				<tr class="inscience-modal-row-status">
					<th><?php esc_html_e( 'Status', 'inscience-training' ); ?></th>
					<td class="inscience-modal-status"></td>
				</tr>
			</table>
			<p class="inscience-modal-description"></p>
			<div class="inscience-modal-actions">
				<a href="#" class="inscience-modal-enrol button inscience-btn-enrol"><?php esc_html_e( 'Enrol Now', 'inscience-training' ); ?></a>
				<div class="inscience-addcal-wrap">
					<button type="button" class="inscience-btn-addcal" id="inscience-addcal-toggle" aria-haspopup="true" aria-expanded="false">
						<span aria-hidden="true">📅</span> <?php esc_html_e( 'Add to Calendar', 'inscience-training' ); ?>
					</button>
					<div class="inscience-addcal-dropdown" id="inscience-addcal-dropdown" hidden>
						<a href="#" class="inscience-addcal-option" id="inscience-addcal-google">
							<span aria-hidden="true">🗓</span> <?php esc_html_e( 'Google Calendar', 'inscience-training' ); ?>
						</a>
						<a href="#" class="inscience-addcal-option" id="inscience-addcal-ics">
							<span aria-hidden="true">⬇</span> <?php esc_html_e( 'Download .ics', 'inscience-training' ); ?> <small><?php esc_html_e( '(Apple / Outlook)', 'inscience-training' ); ?></small>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Floating notification widget (only rendered when a notification page is configured) -->
	<?php if ( get_option( 'inscience_notification_page_id' ) ) : ?>
	<div id="inscience-notify-widget" class="inscience-notify-widget" role="complementary" aria-label="<?php esc_attr_e( 'Course notification sign-up', 'inscience-training' ); ?>">
		<!-- Collapsed tab – always visible so the widget can be reopened -->
		<button class="inscience-notify-tab" id="inscience-notify-tab" aria-expanded="true" aria-controls="inscience-notify-panel">
			<span class="inscience-notify-tab-icon" aria-hidden="true">🔔</span>
			<span class="inscience-notify-tab-label"><?php esc_html_e( 'Notify me', 'inscience-training' ); ?></span>
		</button>
		<!-- Expanded panel -->
		<div class="inscience-notify-panel" id="inscience-notify-panel">
			<button class="inscience-notify-close" id="inscience-notify-close" aria-label="<?php esc_attr_e( 'Dismiss', 'inscience-training' ); ?>">&times;</button>
			<p class="inscience-notify-heading"><?php esc_html_e( 'Get notified about upcoming courses', 'inscience-training' ); ?></p>
			<a href="<?php echo esc_url( get_permalink( get_option( 'inscience_notification_page_id' ) ) ); ?>"
				class="inscience-btn inscience-btn-notify">
				<?php esc_html_e( 'Sign Up', 'inscience-training' ); ?>
			</a>
		</div>
	</div>
	<?php endif; ?>
</div><!-- /.inscience-cal-main -->

<?php if ( $inline_form ) :
	// Provide variables expected by the enrolment form template.
	$courses   = InScience_Course_CPT::get_upcoming_courses();
	$course_id = isset( $preset_course_id ) ? $preset_course_id : 0;

	// Success/error messages when redirected back with a status parameter.
	$message = '';
	if ( isset( $_GET['inscience_status'] ) ) {
		if ( 'success' === sanitize_text_field( wp_unslash( $_GET['inscience_status'] ) ) ) {
			$message = '<div class="inscience-notice inscience-success">' . esc_html__( 'Thank you! Your enrolment has been received. You will receive a confirmation email shortly.', 'inscience-training' ) . '</div>';
		} elseif ( 'payment_complete' === sanitize_text_field( wp_unslash( $_GET['inscience_status'] ) ) ) {
			$message = '<div class="inscience-notice inscience-success">' . esc_html__( 'Payment received! Your enrolment is confirmed.', 'inscience-training' ) . '</div>';
		} elseif ( 'error' === sanitize_text_field( wp_unslash( $_GET['inscience_status'] ) ) ) {
			$error_msg = isset( $_GET['inscience_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['inscience_msg'] ) ) : __( 'An error occurred. Please try again.', 'inscience-training' );
			$message = '<div class="inscience-notice inscience-error">' . esc_html( $error_msg ) . '</div>';
		}
	}
?>
<div class="inscience-cal-form-panel" id="inscience-enrol-inline">
	<h2 class="inscience-cal-form-heading"><?php esc_html_e( 'Enrol in a Course', 'inscience-training' ); ?></h2>
	<?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php include INSCIENCE_PLUGIN_DIR . 'public/views/enrolment-form.php'; ?>
</div>
<?php endif; ?>
</div><!-- /.inscience-calendar-wrap -->

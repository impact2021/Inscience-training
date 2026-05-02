<?php
/**
 * Public calendar view.
 *
 * @package InScience_Training
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="inscience-calendar-wrap">
	<!-- Legend -->
	<div class="inscience-calendar-legend">
		<span class="inscience-legend-item"><span class="inscience-legend-dot inscience-legend-classroom"></span><?php esc_html_e( 'Classroom', 'inscience-training' ); ?></span>
		<span class="inscience-legend-item"><span class="inscience-legend-dot inscience-legend-zoom"></span><?php esc_html_e( 'Zoom (Online)', 'inscience-training' ); ?></span>
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
</div>

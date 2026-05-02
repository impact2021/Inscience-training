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
</div>

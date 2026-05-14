<?php
/**
 * Public course table view.
 *
 * Variables available:
 *   $courses           array   – upcoming course data from InScience_Course_CPT::get_upcoming_courses()
 *   $enrolment_page_id int     – WP page ID for the enrolment page (0 if not set)
 *
 * @package InScience_Training
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="inscience-course-table-wrap">
<?php if ( empty( $courses ) ) : ?>
	<p class="inscience-no-courses"><?php esc_html_e( 'No upcoming courses are currently scheduled.', 'inscience-training' ); ?></p>
<?php else : ?>
	<table class="inscience-course-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Course', 'inscience-training' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Type', 'inscience-training' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'inscience-training' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Time', 'inscience-training' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Location', 'inscience-training' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Price', 'inscience-training' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'inscience-training' ); ?></th>
				<th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'inscience-training' ); ?></span></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $courses as $course ) :
			// Resolve location label.
			if ( 'classroom' === $course['type'] && $course['city'] ) {
				$location = InScience_Course_CPT::NZ_CITIES[ $course['city'] ] ?? ucfirst( $course['city'] );
			} elseif ( 'zoom' === $course['type'] ) {
				$location = __( 'Online (Zoom)', 'inscience-training' );
			} else {
				$location = $course['location'];
			}

			// Format date range.
			$date_display = $course['date']
				? date_i18n( get_option( 'date_format' ), strtotime( $course['date'] ) )
				: '';
			if ( $course['end_date'] && $course['end_date'] !== $course['date'] ) {
				$date_display .= ' – ' . date_i18n( get_option( 'date_format' ), strtotime( $course['end_date'] ) );
			}

			// Format time range.
			$time_display = '';
			if ( $course['start_time'] ) {
				$time_display = $course['start_time'];
				if ( $course['end_time'] ) {
					$time_display .= ' – ' . $course['end_time'];
				}
			}

			// Status badge CSS modifier.
			$status     = $course['status'] ?: 'open';
			$status_mod = sanitize_html_class( $status );

			// Enrol URL.
			$enrol_url = '';
			if ( $enrolment_page_id ) {
				$enrol_url = add_query_arg( 'course_id', $course['id'], get_permalink( $enrolment_page_id ) );
			}
		?>
			<tr>
				<td data-label="<?php esc_attr_e( 'Course', 'inscience-training' ); ?>">
					<?php echo esc_html( $course['title'] ); ?>
					<?php if ( $course['us_codes'] ) : ?>
						<span class="inscience-table-us"><?php echo esc_html( $course['us_codes'] ); ?></span>
					<?php endif; ?>
				</td>
				<td data-label="<?php esc_attr_e( 'Type', 'inscience-training' ); ?>">
					<?php if ( $course['type'] ) : ?>
					<span class="inscience-type-badge inscience-type-<?php echo esc_attr( $course['type'] ); ?>">
						<?php echo esc_html( ucfirst( $course['type'] ) ); ?>
					</span>
					<?php endif; ?>
				</td>
				<td data-label="<?php esc_attr_e( 'Date', 'inscience-training' ); ?>"><?php echo esc_html( $date_display ); ?></td>
				<td data-label="<?php esc_attr_e( 'Time', 'inscience-training' ); ?>"><?php echo esc_html( $time_display ); ?></td>
				<td data-label="<?php esc_attr_e( 'Location', 'inscience-training' ); ?>"><?php echo esc_html( $location ); ?></td>
				<td data-label="<?php esc_attr_e( 'Price', 'inscience-training' ); ?>">
					<?php echo $course['price'] > 0 ? esc_html( '$' . number_format( (float) $course['price'], 2 ) ) : esc_html__( 'Free', 'inscience-training' ); ?>
				</td>
				<td data-label="<?php esc_attr_e( 'Status', 'inscience-training' ); ?>">
					<span class="inscience-status-badge inscience-status-<?php echo esc_attr( $status_mod ); ?>">
						<?php echo esc_html( ucfirst( $status ) ); ?>
					</span>
				</td>
				<td class="inscience-table-actions">
					<?php if ( $enrol_url && 'full' !== $status ) : ?>
					<a href="<?php echo esc_url( $enrol_url ); ?>" class="inscience-btn inscience-btn-enrol inscience-btn-enrol-sm">
						<?php esc_html_e( 'Enrol', 'inscience-training' ); ?>
					</a>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
</div>

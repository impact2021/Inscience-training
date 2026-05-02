<?php
/**
 * Current Courses admin view.
 *
 * @package InScience_Training
 * Variables: $courses (array of course data arrays)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="wrap inscience-admin-wrap">
	<h1 class="inscience-page-title">
		<span class="dashicons dashicons-calendar-alt"></span>
		<?php esc_html_e( 'Current Courses', 'inscience-training' ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-add-course' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Add New', 'inscience-training' ); ?>
		</a>
	</h1>

	<?php if ( empty( $courses ) ) : ?>
		<div class="inscience-card">
			<p><?php esc_html_e( 'No courses found. ', 'inscience-training' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-add-course' ) ); ?>"><?php esc_html_e( 'Add your first course.', 'inscience-training' ); ?></a></p>
		</div>
	<?php else : ?>
	<table class="wp-list-table widefat fixed striped inscience-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Course Title', 'inscience-training' ); ?></th>
				<th scope="col" style="width:120px"><?php esc_html_e( 'Type', 'inscience-training' ); ?></th>
				<th scope="col" style="width:130px"><?php esc_html_e( 'Start Date', 'inscience-training' ); ?></th>
				<th scope="col" style="width:150px"><?php esc_html_e( 'Location / City', 'inscience-training' ); ?></th>
				<th scope="col" style="width:90px"><?php esc_html_e( 'Price', 'inscience-training' ); ?></th>
				<th scope="col" style="width:90px"><?php esc_html_e( 'Enrolments', 'inscience-training' ); ?></th>
				<th scope="col" style="width:80px"><?php esc_html_e( 'Status', 'inscience-training' ); ?></th>
				<th scope="col" style="width:130px"><?php esc_html_e( 'Actions', 'inscience-training' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $courses as $c ) :
			global $wpdb;
			$enrolment_count = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}inscience_enrolments WHERE course_id = %d AND status != 'cancelled'",
				$c['id']
			) );
			$city_label = '';
			if ( 'classroom' === $c['course_type'] && $c['course_city'] ) {
				$city_label = InScience_Course_CPT::NZ_CITIES[ $c['course_city'] ] ?? ucfirst( $c['course_city'] );
			} elseif ( 'zoom' === $c['course_type'] ) {
				$city_label = 'Online (Zoom)';
			}
		?>
		<tr>
			<td>
				<strong><?php echo esc_html( $c['title'] ); ?></strong>
				<?php if ( $c['course_us_codes'] ) : ?>
				<br><small class="description"><?php echo esc_html( $c['course_us_codes'] ); ?></small>
				<?php endif; ?>
				<?php if ( 'draft' === $c['status'] ) : ?>
				<span class="inscience-badge inscience-badge-draft"><?php esc_html_e( 'Draft', 'inscience-training' ); ?></span>
				<?php endif; ?>
			</td>
			<td>
				<?php if ( $c['course_type'] ) : ?>
				<span class="inscience-badge inscience-badge-<?php echo esc_attr( $c['course_type'] ); ?>">
					<?php echo esc_html( ucfirst( $c['course_type'] ) ); ?>
				</span>
				<?php endif; ?>
			</td>
			<td><?php echo esc_html( $c['course_date'] ); ?></td>
			<td><?php echo esc_html( $city_label ); ?></td>
			<td><?php echo $c['course_price'] ? esc_html( '$' . number_format( (float) $c['course_price'], 2 ) ) : '—'; ?></td>
			<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-enrolments&course_id=' . $c['id'] ) ); ?>">
					<?php echo esc_html( $enrolment_count ); ?>
				</a>
			</td>
			<td>
				<?php
				$status_map = array(
					'open'      => 'inscience-badge-open',
					'full'      => 'inscience-badge-full',
					'cancelled' => 'inscience-badge-cancelled',
				);
				$badge_class = $status_map[ $c['course_status'] ] ?? 'inscience-badge-open';
				?>
				<span class="inscience-badge <?php echo esc_attr( $badge_class ); ?>">
					<?php echo esc_html( ucfirst( $c['course_status'] ?: 'open' ) ); ?>
				</span>
			</td>
			<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-add-course&id=' . $c['id'] ) ); ?>" class="button button-small">
					<?php esc_html_e( 'Edit', 'inscience-training' ); ?>
				</a>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline"
					onsubmit="return confirm('<?php esc_attr_e( 'Delete this course?', 'inscience-training' ); ?>')">
					<?php wp_nonce_field( 'inscience_delete_course_' . $c['id'] ); ?>
					<input type="hidden" name="action" value="inscience_delete_course">
					<input type="hidden" name="course_id" value="<?php echo esc_attr( $c['id'] ); ?>">
					<button type="submit" class="button button-small inscience-btn-danger">
						<?php esc_html_e( 'Delete', 'inscience-training' ); ?>
					</button>
				</form>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>

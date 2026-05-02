<?php
/**
 * Enrolments list admin view.
 *
 * @package InScience_Training
 * Variables: $enrolments, $all_courses, $course_filter, $status_filter, $search
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="wrap inscience-admin-wrap">
	<h1 class="inscience-page-title">
		<span class="dashicons dashicons-groups"></span>
		<?php esc_html_e( 'Enrolments', 'inscience-training' ); ?>
	</h1>

	<!-- Filters -->
	<form method="get" class="inscience-filter-form">
		<input type="hidden" name="page" value="inscience-enrolments">
		<select name="course_id">
			<option value=""><?php esc_html_e( 'All Courses', 'inscience-training' ); ?></option>
			<?php foreach ( $all_courses as $cp ) : ?>
			<option value="<?php echo esc_attr( $cp->ID ); ?>" <?php selected( $course_filter, $cp->ID ); ?>>
				<?php echo esc_html( $cp->post_title ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<select name="status">
			<option value=""><?php esc_html_e( 'All Statuses', 'inscience-training' ); ?></option>
			<?php
			$statuses = array( 'pending', 'confirmed', 'attended', 'cancelled' );
			foreach ( $statuses as $s ) :
			?>
			<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status_filter, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search name or email…', 'inscience-training' ); ?>" value="<?php echo esc_attr( $search ); ?>">
		<button type="submit" class="button"><?php esc_html_e( 'Filter', 'inscience-training' ); ?></button>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-enrolments' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'inscience-training' ); ?></a>
	</form>

	<?php if ( empty( $enrolments ) ) : ?>
		<div class="inscience-card"><p><?php esc_html_e( 'No enrolments found.', 'inscience-training' ); ?></p></div>
	<?php else : ?>
	<table class="wp-list-table widefat fixed striped inscience-table">
		<thead>
			<tr>
				<th style="width:50px"><?php esc_html_e( 'ID', 'inscience-training' ); ?></th>
				<th><?php esc_html_e( 'Attendee', 'inscience-training' ); ?></th>
				<th><?php esc_html_e( 'Course', 'inscience-training' ); ?></th>
				<th style="width:130px"><?php esc_html_e( 'Date', 'inscience-training' ); ?></th>
				<th style="width:100px"><?php esc_html_e( 'Payment', 'inscience-training' ); ?></th>
				<th style="width:100px"><?php esc_html_e( 'Status', 'inscience-training' ); ?></th>
				<th style="width:130px"><?php esc_html_e( 'Enrolled On', 'inscience-training' ); ?></th>
				<th style="width:70px"><?php esc_html_e( 'View', 'inscience-training' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $enrolments as $e ) :
			$course_meta = InScience_Course_CPT::get_course_meta( $e->course_id );
		?>
		<tr>
			<td><?php echo absint( $e->id ); ?></td>
			<td>
				<?php echo esc_html( $e->given_names . ' ' . $e->last_name ); ?><br>
				<small><?php echo esc_html( $e->email ); ?></small>
			</td>
			<td><?php echo esc_html( get_the_title( $e->course_id ) ); ?></td>
			<td><?php echo $course_meta['course_date'] ? esc_html( gmdate( 'd M Y', strtotime( $course_meta['course_date'] ) ) ) : '—'; ?></td>
			<td>
				<span class="inscience-badge inscience-payment-<?php echo esc_attr( $e->payment_status ); ?>">
					<?php echo esc_html( ucfirst( $e->payment_status ) ); ?>
				</span><br>
				<small><?php echo esc_html( ucwords( str_replace( '_', ' ', $e->payment_method ) ) ); ?></small>
			</td>
			<td>
				<span class="inscience-badge inscience-status-<?php echo esc_attr( $e->status ); ?>">
					<?php echo esc_html( ucfirst( $e->status ) ); ?>
				</span>
			</td>
			<td><?php echo esc_html( gmdate( 'd M Y', strtotime( $e->created_at ) ) ); ?></td>
			<td>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-enrolments&id=' . $e->id ) ); ?>" class="button button-small">
					<?php esc_html_e( 'View', 'inscience-training' ); ?>
				</a>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>

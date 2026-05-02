<?php
/**
 * Enrolment detail admin view.
 *
 * @package InScience_Training
 * Variables: $enrolment, $course, $meta, $view_id
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! $enrolment ) {
	echo '<div class="wrap"><div class="notice notice-error"><p>' . esc_html__( 'Enrolment not found.', 'inscience-training' ) . '</p></div></div>';
	return;
}
?>
<div class="wrap inscience-admin-wrap">
	<h1 class="inscience-page-title">
		<span class="dashicons dashicons-id-alt"></span>
		<?php
		/* translators: %d: enrolment ID */
		printf( esc_html__( 'Enrolment #%d', 'inscience-training' ), absint( $enrolment->id ) );
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-enrolments' ) ); ?>" class="page-title-action">
			← <?php esc_html_e( 'Back to Enrolments', 'inscience-training' ); ?>
		</a>
	</h1>

	<div class="inscience-form-grid">
		<div class="inscience-form-main">

			<!-- Attendee Details -->
			<div class="inscience-card">
				<h2><?php esc_html_e( 'Attendee Details', 'inscience-training' ); ?></h2>
				<table class="inscience-detail-table">
					<tr><th><?php esc_html_e( 'Full Name', 'inscience-training' ); ?></th><td><?php echo esc_html( $enrolment->given_names . ' ' . $enrolment->last_name ); ?></td></tr>
					<?php if ( $enrolment->preferred_name ) : ?>
					<tr><th><?php esc_html_e( 'Preferred Name', 'inscience-training' ); ?></th><td><?php echo esc_html( $enrolment->preferred_name ); ?></td></tr>
					<?php endif; ?>
					<tr><th><?php esc_html_e( 'Email', 'inscience-training' ); ?></th><td><a href="mailto:<?php echo esc_attr( $enrolment->email ); ?>"><?php echo esc_html( $enrolment->email ); ?></a></td></tr>
					<tr><th><?php esc_html_e( 'Phone', 'inscience-training' ); ?></th><td><?php echo esc_html( $enrolment->phone ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Date of Birth', 'inscience-training' ); ?></th><td><?php echo esc_html( $enrolment->date_of_birth ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Residential Address', 'inscience-training' ); ?></th>
						<td><?php echo esc_html( $enrolment->street_address . ', ' . $enrolment->city . ' ' . $enrolment->postcode ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Ethnic Group', 'inscience-training' ); ?></th><td><?php echo esc_html( $enrolment->ethnic_group ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Gender', 'inscience-training' ); ?></th><td><?php echo esc_html( ucfirst( $enrolment->gender ) ); ?></td></tr>
				</table>
			</div>

			<!-- Employer Details -->
			<?php if ( $enrolment->employer ) : ?>
			<div class="inscience-card">
				<h2><?php esc_html_e( 'Employer Details', 'inscience-training' ); ?></h2>
				<table class="inscience-detail-table">
					<tr><th><?php esc_html_e( 'Employer', 'inscience-training' ); ?></th><td><?php echo esc_html( $enrolment->employer ); ?></td></tr>
					<?php if ( $enrolment->branch ) : ?>
					<tr><th><?php esc_html_e( 'Branch', 'inscience-training' ); ?></th><td><?php echo esc_html( $enrolment->branch ); ?></td></tr>
					<?php endif; ?>
					<?php if ( $enrolment->group_email ) : ?>
					<tr><th><?php esc_html_e( "Group Organiser's Email", 'inscience-training' ); ?></th><td><?php echo esc_html( $enrolment->group_email ); ?></td></tr>
					<?php endif; ?>
				</table>
			</div>
			<?php endif; ?>

			<!-- Course Details -->
			<div class="inscience-card">
				<h2><?php esc_html_e( 'Course Details', 'inscience-training' ); ?></h2>
				<table class="inscience-detail-table">
					<tr><th><?php esc_html_e( 'Course', 'inscience-training' ); ?></th><td><?php echo esc_html( get_the_title( $enrolment->course_id ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Type', 'inscience-training' ); ?></th><td><?php echo esc_html( ucfirst( $meta['course_type'] ?? '' ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Date', 'inscience-training' ); ?></th><td><?php echo esc_html( $meta['course_date'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Enrolment Type', 'inscience-training' ); ?></th><td><?php echo esc_html( ucwords( str_replace( '_', ' ', $enrolment->enrolment_type ) ) ); ?></td></tr>
				</table>
			</div>
		</div>

		<div class="inscience-form-sidebar">
			<!-- Update Status -->
			<div class="inscience-card">
				<h2><?php esc_html_e( 'Update Enrolment', 'inscience-training' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'inscience_update_enrolment_action', 'inscience_nonce' ); ?>
					<input type="hidden" name="action" value="inscience_update_enrolment">
					<input type="hidden" name="enrolment_id" value="<?php echo esc_attr( $enrolment->id ); ?>">

					<p>
						<label><strong><?php esc_html_e( 'Enrolment Status', 'inscience-training' ); ?></strong></label>
						<select name="status" class="widefat">
							<?php foreach ( array( 'pending', 'confirmed', 'attended', 'cancelled' ) as $s ) : ?>
							<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $enrolment->status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>

					<p>
						<label><strong><?php esc_html_e( 'Payment Status', 'inscience-training' ); ?></strong></label>
						<select name="payment_status" class="widefat">
							<?php foreach ( array( 'pending', 'paid', 'refunded' ) as $s ) : ?>
							<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $enrolment->payment_status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>

					<p>
						<label><strong><?php esc_html_e( 'Admin Notes', 'inscience-training' ); ?></strong></label>
						<textarea name="notes" class="widefat" rows="4"><?php echo esc_textarea( $enrolment->notes ); ?></textarea>
					</p>

					<p>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Update', 'inscience-training' ); ?></button>
					</p>
				</form>
			</div>

			<!-- Payment Info -->
			<div class="inscience-card">
				<h2><?php esc_html_e( 'Payment', 'inscience-training' ); ?></h2>
				<table class="inscience-detail-table">
					<tr><th><?php esc_html_e( 'Method', 'inscience-training' ); ?></th><td><?php echo esc_html( ucwords( str_replace( '_', ' ', $enrolment->payment_method ) ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Status', 'inscience-training' ); ?></th>
						<td><span class="inscience-badge inscience-payment-<?php echo esc_attr( $enrolment->payment_status ); ?>"><?php echo esc_html( ucfirst( $enrolment->payment_status ) ); ?></span></td></tr>
					<?php if ( $enrolment->stripe_session ) : ?>
					<tr><th><?php esc_html_e( 'Stripe Session', 'inscience-training' ); ?></th><td><small><?php echo esc_html( $enrolment->stripe_session ); ?></small></td></tr>
					<?php endif; ?>
				</table>
			</div>

			<div class="inscience-card">
				<h2><?php esc_html_e( 'Meta', 'inscience-training' ); ?></h2>
				<table class="inscience-detail-table">
					<tr><th><?php esc_html_e( 'Enrolled', 'inscience-training' ); ?></th><td><?php echo esc_html( gmdate( 'd M Y H:i', strtotime( $enrolment->created_at ) ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Last Updated', 'inscience-training' ); ?></th><td><?php echo esc_html( gmdate( 'd M Y H:i', strtotime( $enrolment->updated_at ) ) ); ?></td></tr>
				</table>
			</div>
		</div>
	</div>
</div>

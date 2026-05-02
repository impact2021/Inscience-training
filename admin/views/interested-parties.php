<?php
/**
 * Interested Parties (notification subscribers) admin view.
 *
 * @package InScience_Training
 * Variables: $subscribers
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$course_types = array_merge( array( '' => 'All courses' ), InScience_Course_CPT::TYPES );
?>
<div class="wrap inscience-admin-wrap">
	<h1 class="inscience-page-title">
		<span class="dashicons dashicons-email-alt"></span>
		<?php esc_html_e( 'Interested Parties', 'inscience-training' ); ?>
	</h1>

	<p><?php esc_html_e( 'People who have signed up to be notified when a new course is available.', 'inscience-training' ); ?></p>

	<?php if ( empty( $subscribers ) ) : ?>
		<div class="inscience-card"><p><?php esc_html_e( 'No subscribers yet.', 'inscience-training' ); ?></p></div>
	<?php else : ?>
	<p class="description">
		<?php
		/* translators: %d: number of subscribers */
		printf( esc_html__( '%d subscriber(s) total.', 'inscience-training' ), count( $subscribers ) );
		?>
	</p>
	<table class="wp-list-table widefat fixed striped inscience-table">
		<thead>
			<tr>
				<th style="width:50px"><?php esc_html_e( 'ID', 'inscience-training' ); ?></th>
				<th><?php esc_html_e( 'Name', 'inscience-training' ); ?></th>
				<th><?php esc_html_e( 'Email', 'inscience-training' ); ?></th>
				<th style="width:160px"><?php esc_html_e( 'Interested In', 'inscience-training' ); ?></th>
				<th style="width:140px"><?php esc_html_e( 'Signed Up', 'inscience-training' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $subscribers as $sub ) : ?>
		<tr>
			<td><?php echo absint( $sub->id ); ?></td>
			<td><?php echo esc_html( $sub->name ?? '—' ); ?></td>
			<td><a href="mailto:<?php echo esc_attr( $sub->email ); ?>"><?php echo esc_html( $sub->email ); ?></a></td>
			<td><?php echo esc_html( isset( $course_types[ $sub->course_type ] ) ? $course_types[ $sub->course_type ] : 'All courses' ); ?></td>
			<td><?php echo esc_html( gmdate( 'd M Y', strtotime( $sub->created_at ) ) ); ?></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>

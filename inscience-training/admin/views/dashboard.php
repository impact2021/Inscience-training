<?php
/**
 * Admin dashboard view.
 *
 * @package InScience_Training
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$total_courses     = wp_count_posts( 'inscience_course' );
$total_enrolments  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}inscience_enrolments" );
$pending_enrolments= (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}inscience_enrolments WHERE status = 'pending'" );
$total_subscribers = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}inscience_notifications" );
?>
<div class="wrap inscience-admin-wrap">
	<h1 class="inscience-page-title">
		<span class="dashicons dashicons-welcome-learn-more"></span>
		<?php esc_html_e( 'InScience Training — Dashboard', 'inscience-training' ); ?>
	</h1>

	<div class="inscience-stats-grid">
		<div class="inscience-stat-card">
			<span class="dashicons dashicons-calendar-alt"></span>
			<div class="inscience-stat-number"><?php echo absint( ( $total_courses->publish ?? 0 ) ); ?></div>
			<div class="inscience-stat-label"><?php esc_html_e( 'Active Courses', 'inscience-training' ); ?></div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-courses' ) ); ?>"><?php esc_html_e( 'View Courses', 'inscience-training' ); ?></a>
		</div>
		<div class="inscience-stat-card">
			<span class="dashicons dashicons-groups"></span>
			<div class="inscience-stat-number"><?php echo esc_html( $total_enrolments ); ?></div>
			<div class="inscience-stat-label"><?php esc_html_e( 'Total Enrolments', 'inscience-training' ); ?></div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-enrolments' ) ); ?>"><?php esc_html_e( 'View Enrolments', 'inscience-training' ); ?></a>
		</div>
		<div class="inscience-stat-card inscience-stat-pending">
			<span class="dashicons dashicons-clock"></span>
			<div class="inscience-stat-number"><?php echo esc_html( $pending_enrolments ); ?></div>
			<div class="inscience-stat-label"><?php esc_html_e( 'Pending Enrolments', 'inscience-training' ); ?></div>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-enrolments&status=pending' ) ); ?>"><?php esc_html_e( 'View Pending', 'inscience-training' ); ?></a>
		</div>
		<div class="inscience-stat-card">
			<span class="dashicons dashicons-email"></span>
			<div class="inscience-stat-number"><?php echo esc_html( $total_subscribers ); ?></div>
			<div class="inscience-stat-label"><?php esc_html_e( 'Notification Subscribers', 'inscience-training' ); ?></div>
		</div>
	</div>

	<div class="inscience-quick-actions">
		<h2><?php esc_html_e( 'Quick Actions', 'inscience-training' ); ?></h2>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-add-course' ) ); ?>" class="button button-primary button-large">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'Add New Course', 'inscience-training' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-enrolments' ) ); ?>" class="button button-secondary button-large">
			<span class="dashicons dashicons-list-view"></span>
			<?php esc_html_e( 'View All Enrolments', 'inscience-training' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=inscience-settings' ) ); ?>" class="button button-secondary button-large">
			<span class="dashicons dashicons-admin-generic"></span>
			<?php esc_html_e( 'Settings', 'inscience-training' ); ?>
		</a>
	</div>
</div>

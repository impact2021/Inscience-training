<?php
/**
 * Install / Activate / Deactivate handler.
 *
 * @package InScience_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InScience_Install {

	/**
	 * Plugin activation.
	 */
	public static function activate() {
		self::create_tables();
		self::create_default_email_templates();
		self::add_caps();
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables.
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Enrolments table
		$enrolments_table = $wpdb->prefix . 'inscience_enrolments';
		$sql_enrolments = "CREATE TABLE {$enrolments_table} (
			id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			course_id       BIGINT(20) UNSIGNED NOT NULL,
			enrolment_type  VARCHAR(50)  NOT NULL DEFAULT 'new',
			employer        VARCHAR(255) DEFAULT NULL,
			branch          VARCHAR(255) DEFAULT NULL,
			group_email     VARCHAR(255) DEFAULT NULL,
			given_names     VARCHAR(255) NOT NULL,
			last_name       VARCHAR(255) NOT NULL,
			preferred_name  VARCHAR(255) DEFAULT NULL,
			street_address  VARCHAR(255) NOT NULL,
			city            VARCHAR(100) NOT NULL,
			postcode        VARCHAR(20)  NOT NULL,
			email           VARCHAR(255) NOT NULL,
			date_of_birth   DATE         NOT NULL,
			phone           VARCHAR(50)  NOT NULL,
			ethnic_group    TEXT         DEFAULT NULL,
			gender          VARCHAR(50)  DEFAULT NULL,
			payment_method  VARCHAR(50)  NOT NULL DEFAULT 'bank_transfer',
			payment_status  VARCHAR(50)  NOT NULL DEFAULT 'pending',
			stripe_session  VARCHAR(255) DEFAULT NULL,
			declaration     TINYINT(1)   NOT NULL DEFAULT 0,
			status          VARCHAR(50)  NOT NULL DEFAULT 'pending',
			notes           TEXT         DEFAULT NULL,
			created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY course_id (course_id),
			KEY email (email(191)),
			KEY status (status)
		) $charset_collate;";

		// Notification signups table
		$notifications_table = $wpdb->prefix . 'inscience_notifications';
		$sql_notifications = "CREATE TABLE {$notifications_table} (
			id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			email       VARCHAR(255) NOT NULL,
			name        VARCHAR(255) DEFAULT NULL,
			course_type VARCHAR(255) DEFAULT NULL,
			created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY email (email(191))
		) $charset_collate;";

		// Email templates table
		$emails_table = $wpdb->prefix . 'inscience_email_templates';
		$sql_emails = "CREATE TABLE {$emails_table} (
			id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			slug        VARCHAR(100) NOT NULL,
			label       VARCHAR(255) NOT NULL,
			subject     VARCHAR(255) NOT NULL,
			body        LONGTEXT     NOT NULL,
			updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_enrolments );
		dbDelta( $sql_notifications );
		dbDelta( $sql_emails );

		update_option( 'inscience_db_version', INSCIENCE_VERSION );
	}

	/**
	 * Insert default email templates (only if they don't already exist).
	 */
	public static function create_default_email_templates() {
		global $wpdb;
		$table = $wpdb->prefix . 'inscience_email_templates';

		$templates = array(
			array(
				'slug'    => 'enrolment_confirmation',
				'label'   => 'Enrolment Confirmation (to attendee)',
				'subject' => 'Your InScience Enrolment Confirmation – {course_title}',
				'body'    => "Dear {given_names},\n\nThank you for enrolling in {course_title} scheduled for {course_date} ({course_type} – {course_location}).\n\nYour enrolment reference is #{enrolment_id}.\n\nPayment method selected: {payment_method}\n\n{payment_instructions}\n\nIf you have any questions please contact us at info@inscience.co.nz.\n\nKind regards,\nInScience Ltd",
			),
			array(
				'slug'    => 'enrolment_admin',
				'label'   => 'New Enrolment Notification (to admin)',
				'subject' => 'New Enrolment: {given_names} {last_name} – {course_title}',
				'body'    => "A new enrolment has been received.\n\nCourse: {course_title}\nDate: {course_date}\nType: {course_type} – {course_location}\n\nAttendee: {given_names} {last_name}\nEmail: {email}\nPhone: {phone}\nEmployer: {employer}\nPayment Method: {payment_method}\n\nView enrolment in admin: {admin_url}",
			),
			array(
				'slug'    => 'payment_received',
				'label'   => 'Payment Received (to attendee)',
				'subject' => 'Payment Received – InScience Enrolment #{enrolment_id}',
				'body'    => "Dear {given_names},\n\nWe have received your payment for {course_title}.\n\nYour place is now confirmed. We look forward to seeing you on {course_date}.\n\nKind regards,\nInScience Ltd",
			),
			array(
				'slug'    => 'new_course_notification',
				'label'   => 'New Course Notification (to subscribers)',
				'subject' => 'New Course Available – {course_title}',
				'body'    => "Hi {name},\n\nA new course has been added to the InScience schedule:\n\n{course_title}\nDate: {course_date}\nType: {course_type} – {course_location}\n\nEnrol now: {enrol_url}\n\nTo unsubscribe from these notifications, click here: {unsubscribe_url}\n\nKind regards,\nInScience Ltd",
			),
		);

		foreach ( $templates as $template ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s", $template['slug'] )
			);
			if ( ! $exists ) {
				$wpdb->insert( $table, $template );
			}
		}
	}

	/**
	 * Add capabilities for administrator role.
	 */
	public static function add_caps() {
		$role = get_role( 'administrator' );
		if ( $role ) {
			$role->add_cap( 'manage_inscience_courses' );
			$role->add_cap( 'manage_inscience_enrolments' );
		}
	}
}

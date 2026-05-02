<?php
/**
 * Admin menu and page handler.
 *
 * @package InScience_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InScience_Admin {

	/** @var InScience_Admin */
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_inscience_save_course', array( $this, 'handle_save_course' ) );
		add_action( 'admin_post_inscience_delete_course', array( $this, 'handle_delete_course' ) );
		add_action( 'admin_post_inscience_update_enrolment', array( $this, 'handle_update_enrolment' ) );
		add_action( 'admin_post_inscience_save_email_template', array( $this, 'handle_save_email_template' ) );
		add_action( 'admin_post_inscience_save_settings', array( $this, 'handle_save_settings' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_version' ), 100 );
	}

	public function register_menus() {
		// Top-level menu: InScience Training
		add_menu_page(
			__( 'InScience Training', 'inscience-training' ),
			__( 'InScience Training', 'inscience-training' ),
			'manage_inscience_courses',
			'inscience-training',
			array( $this, 'render_dashboard' ),
			'dashicons-welcome-learn-more',
			25
		);

		// Add New Course
		add_submenu_page(
			'inscience-training',
			__( 'Add New Course', 'inscience-training' ),
			__( 'Add New Course', 'inscience-training' ),
			'manage_inscience_courses',
			'inscience-add-course',
			array( $this, 'render_add_course' )
		);

		// Current Courses
		add_submenu_page(
			'inscience-training',
			__( 'Current Courses', 'inscience-training' ),
			__( 'Current Courses', 'inscience-training' ),
			'manage_inscience_courses',
			'inscience-courses',
			array( $this, 'render_courses' )
		);

		// Enrolments
		add_submenu_page(
			'inscience-training',
			__( 'Enrolments', 'inscience-training' ),
			__( 'Enrolments', 'inscience-training' ),
			'manage_inscience_enrolments',
			'inscience-enrolments',
			array( $this, 'render_enrolments' )
		);

		// Emails
		add_submenu_page(
			'inscience-training',
			__( 'Emails', 'inscience-training' ),
			__( 'Emails', 'inscience-training' ),
			'manage_inscience_courses',
			'inscience-emails',
			array( $this, 'render_emails' )
		);

		// Settings
		add_submenu_page(
			'inscience-training',
			__( 'Settings', 'inscience-training' ),
			__( 'Settings', 'inscience-training' ),
			'manage_inscience_courses',
			'inscience-settings',
			array( $this, 'render_settings' )
		);
	}

	public function enqueue_assets( $hook ) {
		$inscience_pages = array(
			'toplevel_page_inscience-training',
			'inscience-training_page_inscience-add-course',
			'inscience-training_page_inscience-courses',
			'inscience-training_page_inscience-enrolments',
			'inscience-training_page_inscience-emails',
			'inscience-training_page_inscience-settings',
		);

		if ( ! in_array( $hook, $inscience_pages, true ) ) {
			return;
		}

		wp_enqueue_style(
			'inscience-admin',
			INSCIENCE_PLUGIN_URL . 'admin/assets/css/inscience-admin.css',
			array(),
			INSCIENCE_VERSION
		);
		wp_enqueue_script(
			'inscience-admin',
			INSCIENCE_PLUGIN_URL . 'admin/assets/js/inscience-admin.js',
			array( 'jquery', 'wp-util' ),
			INSCIENCE_VERSION,
			true
		);
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-datepicker-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css', array(), '1.13.2' );
	}

	// -------------------------------------------------------------------------
	// Page renderers
	// -------------------------------------------------------------------------

	public function render_dashboard() {
		include INSCIENCE_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	public function render_add_course() {
		$course_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$course    = $course_id ? get_post( $course_id ) : null;
		$meta      = $course ? InScience_Course_CPT::get_course_meta( $course_id ) : array();
		include INSCIENCE_PLUGIN_DIR . 'admin/views/add-course.php';
	}

	public function render_courses() {
		$courses_raw = get_posts( array(
			'post_type'      => 'inscience_course',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'draft' ),
			'meta_key'       => '_course_date',
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
		) );

		$courses = array();
		foreach ( $courses_raw as $p ) {
			$courses[] = array_merge(
				array( 'id' => $p->ID, 'title' => $p->post_title, 'status' => $p->post_status ),
				InScience_Course_CPT::get_course_meta( $p->ID )
			);
		}

		include INSCIENCE_PLUGIN_DIR . 'admin/views/courses.php';
	}

	public function render_enrolments() {
		global $wpdb;

		$course_filter = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : 0;
		$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$search        = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$view_id       = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

		if ( $view_id ) {
			$enrolment = InScience_Enrolment::get_enrolment( $view_id );
			$course    = $enrolment ? get_post( $enrolment->course_id ) : null;
			$meta      = $enrolment ? InScience_Course_CPT::get_course_meta( $enrolment->course_id ) : array();
			include INSCIENCE_PLUGIN_DIR . 'admin/views/enrolment-detail.php';
			return;
		}

		$where  = array( '1=1' );
		$values = array();

		if ( $course_filter ) {
			$where[]  = 'course_id = %d';
			$values[] = $course_filter;
		}
		if ( $status_filter ) {
			$where[]  = 'status = %s';
			$values[] = $status_filter;
		}
		if ( $search ) {
			$where[]  = '(email LIKE %s OR given_names LIKE %s OR last_name LIKE %s)';
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$values[] = $like;
			$values[] = $like;
			$values[] = $like;
		}

		$table = $wpdb->prefix . 'inscience_enrolments';
		$sql   = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY created_at DESC';

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$enrolments = $wpdb->get_results( $wpdb->prepare( $sql, $values ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$enrolments = $wpdb->get_results( $sql );
		}

		$all_courses = get_posts( array(
			'post_type'      => 'inscience_course',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'draft' ),
			'orderby'        => 'meta_value',
			'meta_key'       => '_course_date',
			'order'          => 'DESC',
		) );

		include INSCIENCE_PLUGIN_DIR . 'admin/views/enrolments.php';
	}

	public function render_emails() {
		global $wpdb;
		$templates = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}inscience_email_templates ORDER BY id" );
		$edit_slug = isset( $_GET['edit'] ) ? sanitize_text_field( wp_unslash( $_GET['edit'] ) ) : '';
		$edit_template = $edit_slug ? InScience_Emails::get_template( $edit_slug ) : null;
		include INSCIENCE_PLUGIN_DIR . 'admin/views/emails.php';
	}

	public function render_settings() {
		include INSCIENCE_PLUGIN_DIR . 'admin/views/settings.php';
	}

	// -------------------------------------------------------------------------
	// Form handlers
	// -------------------------------------------------------------------------

	public function handle_save_course() {
		check_admin_referer( 'inscience_save_course_action', 'inscience_nonce' );

		if ( ! current_user_can( 'manage_inscience_courses' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'inscience-training' ) );
		}

		$course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;

		$title   = sanitize_text_field( wp_unslash( $_POST['course_title'] ?? '' ) );
		$content = wp_kses_post( wp_unslash( $_POST['course_description'] ?? '' ) );
		$status  = 'on' === ( $_POST['course_published'] ?? '' ) ? 'publish' : 'draft';

		if ( $course_id ) {
			wp_update_post( array(
				'ID'           => $course_id,
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => $status,
			) );
		} else {
			$course_id = wp_insert_post( array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => $status,
				'post_type'    => 'inscience_course',
			) );
		}

		if ( is_wp_error( $course_id ) ) {
			wp_safe_redirect( add_query_arg( 'inscience_msg', 'course_error', admin_url( 'admin.php?page=inscience-add-course' ) ) );
			exit;
		}

		// Save meta.
		$meta_fields = array(
			'course_type', 'course_date', 'course_end_date', 'course_time',
			'course_location', 'course_city', 'course_capacity', 'course_price',
			'course_us_codes', 'course_status',
		);

		foreach ( $meta_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$sanitized = in_array( $field, array( 'course_capacity' ), true )
					? absint( wp_unslash( $_POST[ $field ] ) )
					: ( 'course_price' === $field
						? floatval( wp_unslash( $_POST[ $field ] ) )
						: sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
				update_post_meta( $course_id, '_' . $field, $sanitized );
			}
		}

		wp_safe_redirect( add_query_arg( 'inscience_msg', 'course_saved', admin_url( 'admin.php?page=inscience-courses' ) ) );
		exit;
	}

	public function handle_delete_course() {
		check_admin_referer( 'inscience_delete_course_' . absint( $_POST['course_id'] ?? 0 ) );

		if ( ! current_user_can( 'manage_inscience_courses' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'inscience-training' ) );
		}

		$course_id = absint( $_POST['course_id'] ?? 0 );
		wp_trash_post( $course_id );

		wp_safe_redirect( add_query_arg( 'inscience_msg', 'course_deleted', admin_url( 'admin.php?page=inscience-courses' ) ) );
		exit;
	}

	public function handle_update_enrolment() {
		check_admin_referer( 'inscience_update_enrolment_action', 'inscience_nonce' );

		if ( ! current_user_can( 'manage_inscience_enrolments' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'inscience-training' ) );
		}

		$enrolment_id   = absint( $_POST['enrolment_id'] ?? 0 );
		$status         = sanitize_text_field( $_POST['status'] ?? '' );
		$payment_status = sanitize_text_field( $_POST['payment_status'] ?? '' );
		$notes          = sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) );

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'inscience_enrolments',
			array(
				'status'         => $status,
				'payment_status' => $payment_status,
				'notes'          => $notes,
			),
			array( 'id' => $enrolment_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'inscience-enrolments', 'id' => $enrolment_id, 'inscience_msg' => 'enrolment_updated' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	public function handle_save_email_template() {
		check_admin_referer( 'inscience_save_email_template_action', 'inscience_nonce' );

		if ( ! current_user_can( 'manage_inscience_courses' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'inscience-training' ) );
		}

		$slug    = sanitize_key( wp_unslash( $_POST['template_slug'] ?? '' ) );
		$subject = sanitize_text_field( wp_unslash( $_POST['template_subject'] ?? '' ) );
		$body    = wp_kses_post( wp_unslash( $_POST['template_body'] ?? '' ) );

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'inscience_email_templates',
			array( 'subject' => $subject, 'body' => $body ),
			array( 'slug' => $slug ),
			array( '%s', '%s' ),
			array( '%s' )
		);

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'inscience-emails', 'inscience_msg' => 'email_saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	public function handle_save_settings() {
		check_admin_referer( 'inscience_save_settings_action', 'inscience_nonce' );

		if ( ! current_user_can( 'manage_inscience_courses' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'inscience-training' ) );
		}

		$options = array(
			'inscience_stripe_public_key',
			'inscience_stripe_secret_key',
			'inscience_stripe_webhook_secret',
			'inscience_bank_name',
			'inscience_bank_account_name',
			'inscience_bank_account_number',
			'inscience_bank_reference_instructions',
			'inscience_admin_email',
			'inscience_email_from_name',
			'inscience_email_from_address',
			'inscience_email_logo',
			'inscience_enrolment_page_id',
			'inscience_notification_page_id',
		);

		foreach ( $options as $option ) {
			if ( isset( $_POST[ $option ] ) ) {
				update_option( $option, sanitize_text_field( wp_unslash( $_POST[ $option ] ) ) );
			}
		}

		wp_safe_redirect( add_query_arg( 'inscience_msg', 'settings_saved', admin_url( 'admin.php?page=inscience-settings' ) ) );
		exit;
	}

	/**
	 * Add plugin version to the admin top bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public function admin_bar_version( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_inscience_courses' ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$wp_admin_bar->add_node( array(
			'id'    => 'inscience-training-version',
			'title' => 'InScience Training v' . INSCIENCE_VERSION,
			'href'  => admin_url( 'admin.php?page=inscience-training' ),
			'meta'  => array( 'title' => __( 'InScience Training Plugin', 'inscience-training' ) ),
		) );
	}

	/**
	 * Display admin notices.
	 */
	public function admin_notices() {
		if ( ! isset( $_GET['inscience_msg'] ) ) {
			return;
		}
		$msg = sanitize_text_field( wp_unslash( $_GET['inscience_msg'] ) );
		$notices = array(
			'course_saved'      => array( 'success', __( 'Course saved successfully.', 'inscience-training' ) ),
			'course_deleted'    => array( 'success', __( 'Course deleted.', 'inscience-training' ) ),
			'course_error'      => array( 'error',   __( 'Error saving course. Please try again.', 'inscience-training' ) ),
			'enrolment_updated' => array( 'success', __( 'Enrolment updated.', 'inscience-training' ) ),
			'email_saved'       => array( 'success', __( 'Email template saved.', 'inscience-training' ) ),
			'settings_saved'    => array( 'success', __( 'Settings saved.', 'inscience-training' ) ),
		);
		if ( isset( $notices[ $msg ] ) ) {
			list( $type, $text ) = $notices[ $msg ];
			echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . esc_html( $text ) . '</p></div>';
		}
	}
}

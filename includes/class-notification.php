<?php
/**
 * Course notification sign-up handling.
 *
 * @package InScience_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InScience_Notification {

	/** @var InScience_Notification */
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'inscience_notification_signup', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		// Trigger notification emails when a new course is published.
		add_action( 'transition_post_status', array( $this, 'on_course_published' ), 10, 3 );
	}

	public function enqueue_assets() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'inscience_notification_signup' ) ) {
			wp_enqueue_style(
				'inscience-public',
				INSCIENCE_PLUGIN_URL . 'public/assets/css/inscience-public.css',
				array(),
				INSCIENCE_VERSION
			);
			wp_enqueue_script( 'jquery' );
		}
	}

	/**
	 * Shortcode: [inscience_notification_signup]
	 */
	public function render_shortcode( $atts ) {
		$message = '';
		if ( isset( $_GET['inscience_notify'] ) ) {
			if ( 'success' === sanitize_text_field( wp_unslash( $_GET['inscience_notify'] ) ) ) {
				$message = '<div class="inscience-notice inscience-success">' . esc_html__( 'You are now subscribed to new course notifications!', 'inscience-training' ) . '</div>';
			} elseif ( 'exists' === sanitize_text_field( wp_unslash( $_GET['inscience_notify'] ) ) ) {
				$message = '<div class="inscience-notice inscience-info">' . esc_html__( 'You are already subscribed to notifications.', 'inscience-training' ) . '</div>';
			} elseif ( 'unsubscribed' === sanitize_text_field( wp_unslash( $_GET['inscience_notify'] ) ) ) {
				$message = '<div class="inscience-notice inscience-info">' . esc_html__( 'You have been unsubscribed from course notifications.', 'inscience-training' ) . '</div>';
			}
		}

		ob_start();
		echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		include INSCIENCE_PLUGIN_DIR . 'public/views/notification-signup.php';
		return ob_get_clean();
	}

	/**
	 * When a course post is published (for the first time), send notification emails.
	 */
	public function on_course_published( $new_status, $old_status, $post ) {
		if ( 'inscience_course' !== $post->post_type ) {
			return;
		}
		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			InScience_Emails::send_new_course_notifications( $post->ID );
		}
	}

	/**
	 * Add a new notification subscriber.
	 *
	 * @param string $email
	 * @param string $name
	 * @param string $course_type Optional preference.
	 * @return bool|WP_Error
	 */
	public static function subscribe( $email, $name = '', $course_type = '' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'inscience_notifications';
		$email = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', __( 'Please provide a valid email address.', 'inscience-training' ) );
		}

		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $email ) );
		if ( $existing ) {
			return new WP_Error( 'already_subscribed', __( 'You are already subscribed.', 'inscience-training' ) );
		}

		$result = $wpdb->insert( $table, array(
			'email'       => $email,
			'name'        => sanitize_text_field( $name ),
			'course_type' => sanitize_text_field( $course_type ),
		) );

		return $result !== false;
	}

	/**
	 * Unsubscribe by email.
	 */
	public static function unsubscribe( $email ) {
		global $wpdb;
		return $wpdb->delete(
			$wpdb->prefix . 'inscience_notifications',
			array( 'email' => sanitize_email( $email ) ),
			array( '%s' )
		);
	}

	/**
	 * Get all subscribers.
	 */
	public static function get_subscribers( $course_type = '' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'inscience_notifications';

		if ( $course_type ) {
			return $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM {$table} WHERE course_type = '' OR course_type = %s ORDER BY created_at DESC",
				sanitize_text_field( $course_type )
			) );
		}

		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC" );
	}

	/**
	 * Generate an unsubscribe URL for a given email.
	 */
	public static function get_unsubscribe_url( $email ) {
		$page_id = get_option( 'inscience_enrolment_page_id' );
		$base    = $page_id ? get_permalink( $page_id ) : home_url( '/' );
		return add_query_arg( array(
			'inscience_unsubscribe' => rawurlencode( $email ),
			'inscience_nonce'       => wp_create_nonce( 'inscience_unsubscribe_' . $email ),
		), $base );
	}
}

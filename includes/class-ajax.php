<?php
/**
 * AJAX handlers.
 *
 * @package InScience_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InScience_Ajax {

	/** @var InScience_Ajax */
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Enrolment form submission.
		add_action( 'wp_ajax_inscience_submit_enrolment', array( $this, 'handle_enrolment' ) );
		add_action( 'wp_ajax_nopriv_inscience_submit_enrolment', array( $this, 'handle_enrolment' ) );

		// Notification signup.
		add_action( 'wp_ajax_inscience_notify_signup', array( $this, 'handle_notify_signup' ) );
		add_action( 'wp_ajax_nopriv_inscience_notify_signup', array( $this, 'handle_notify_signup' ) );

		// Unsubscribe link (GET request on front-end page).
		add_action( 'template_redirect', array( $this, 'handle_unsubscribe' ) );
	}

	/**
	 * Handle enrolment form AJAX submission.
	 */
	public function handle_enrolment() {
		check_ajax_referer( 'inscience_enrolment', 'nonce' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked above.
		$data = $_POST;

		$result = InScience_Enrolment::process_submission( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$enrolment_id = $result;
		$response = array(
			'enrolment_id' => $enrolment_id,
			'payment_method' => sanitize_text_field( $data['payment_method'] ?? '' ),
		);

		// If Stripe, create checkout session and return URL.
		if ( 'stripe' === ( $data['payment_method'] ?? '' ) ) {
			$checkout_url = InScience_Payment::create_stripe_session( $enrolment_id );
			if ( is_wp_error( $checkout_url ) ) {
				// Enrolment created but payment failed – return partial success.
				$response['stripe_error'] = $checkout_url->get_error_message();
			} else {
				$response['stripe_checkout_url'] = $checkout_url;
			}
		}

		wp_send_json_success( $response );
	}

	/**
	 * Handle notification signup AJAX.
	 */
	public function handle_notify_signup() {
		check_ajax_referer( 'inscience_notify', 'nonce' );

		$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$course_type = isset( $_POST['course_type'] ) ? sanitize_text_field( wp_unslash( $_POST['course_type'] ) ) : '';

		$result = InScience_Notification::subscribe( $email, $name, $course_type );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'You have been subscribed to course notifications.', 'inscience-training' ) ) );
	}

	/**
	 * Handle unsubscribe link (via GET on any front-end page).
	 */
	public function handle_unsubscribe() {
		if ( ! isset( $_GET['inscience_unsubscribe'] ) ) {
			return;
		}

		$email = sanitize_email( wp_unslash( $_GET['inscience_unsubscribe'] ) );
		$nonce = isset( $_GET['inscience_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['inscience_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'inscience_unsubscribe_' . $email ) ) {
			return;
		}

		InScience_Notification::unsubscribe( $email );

		$redirect = remove_query_arg( array( 'inscience_unsubscribe', 'inscience_nonce' ) );
		$redirect = add_query_arg( 'inscience_notify', 'unsubscribed', $redirect );
		wp_safe_redirect( $redirect );
		exit;
	}
}

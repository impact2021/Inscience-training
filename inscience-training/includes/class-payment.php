<?php
/**
 * Payment processing – Stripe, bank transfer, on-account.
 *
 * @package InScience_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InScience_Payment {

	/** @var InScience_Payment */
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Stripe webhook.
		add_action( 'wp_ajax_nopriv_inscience_stripe_webhook', array( $this, 'handle_stripe_webhook' ) );
		add_action( 'wp_ajax_inscience_stripe_webhook', array( $this, 'handle_stripe_webhook' ) );
	}

	/**
	 * Create a Stripe Checkout Session.
	 *
	 * @param int $enrolment_id
	 * @return string|WP_Error Checkout URL or WP_Error.
	 */
	public static function create_stripe_session( $enrolment_id ) {
		$secret_key = get_option( 'inscience_stripe_secret_key', '' );
		if ( empty( $secret_key ) ) {
			return new WP_Error( 'stripe_not_configured', __( 'Stripe is not configured. Please contact the site administrator.', 'inscience-training' ) );
		}

		$enrolment = InScience_Enrolment::get_enrolment( $enrolment_id );
		if ( ! $enrolment ) {
			return new WP_Error( 'not_found', __( 'Enrolment not found.', 'inscience-training' ) );
		}

		$course_meta  = InScience_Course_CPT::get_course_meta( $enrolment->course_id );
		$course_title = get_the_title( $enrolment->course_id );
		$price_cents  = (int) round( (float) $course_meta['course_price'] * 100 );

		$success_url = add_query_arg( array(
			'inscience_status'       => 'payment_complete',
			'inscience_enrolment_id' => $enrolment_id,
			'session_id'             => '{CHECKOUT_SESSION_ID}',
		), get_permalink( get_option( 'inscience_enrolment_page_id' ) ) );

		$cancel_url = add_query_arg( array(
			'inscience_status' => 'error',
			'inscience_msg'    => rawurlencode( __( 'Payment was cancelled.', 'inscience-training' ) ),
		), get_permalink( get_option( 'inscience_enrolment_page_id' ) ) );

		$response = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $secret_key,
				'Content-Type'  => 'application/x-www-form-urlencoded',
			),
			'body' => array(
				'payment_method_types[]'         => 'card',
				'line_items[0][price_data][currency]'                    => 'nzd',
				'line_items[0][price_data][unit_amount]'                 => $price_cents,
				'line_items[0][price_data][product_data][name]'         => $course_title,
				'line_items[0][quantity]'                                => 1,
				'mode'                                                   => 'payment',
				'success_url'                                            => $success_url,
				'cancel_url'                                             => $cancel_url,
				'customer_email'                                         => $enrolment->email,
				'metadata[enrolment_id]'                                 => $enrolment_id,
			),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'stripe_error', $body['error']['message'] ?? __( 'Stripe error.', 'inscience-training' ) );
		}

		// Store session id on enrolment.
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'inscience_enrolments',
			array( 'stripe_session' => sanitize_text_field( $body['id'] ) ),
			array( 'id' => $enrolment_id ),
			array( '%s' ),
			array( '%d' )
		);

		return $body['url'];
	}

	/**
	 * Handle Stripe webhook (registered as a nopriv AJAX action).
	 * Endpoint: /wp-admin/admin-ajax.php?action=inscience_stripe_webhook
	 */
	public function handle_stripe_webhook() {
		$secret = get_option( 'inscience_stripe_webhook_secret', '' );
		$payload = file_get_contents( 'php://input' );
		$sig     = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ) : '';

		if ( ! empty( $secret ) ) {
			// Verify signature.
			if ( ! self::verify_stripe_signature( $payload, $sig, $secret ) ) {
				status_header( 400 );
				exit( 'Invalid signature' );
			}
		}

		$event = json_decode( $payload, true );
		if ( empty( $event['type'] ) ) {
			status_header( 400 );
			exit( 'Invalid event' );
		}

		if ( 'checkout.session.completed' === $event['type'] ) {
			$session      = $event['data']['object'];
			$enrolment_id = absint( $session['metadata']['enrolment_id'] ?? 0 );
			if ( $enrolment_id ) {
				InScience_Enrolment::update_payment_status( $enrolment_id, 'paid' );
				InScience_Enrolment::update_status( $enrolment_id, 'confirmed' );
				InScience_Emails::send_payment_received( $enrolment_id );
			}
		}

		status_header( 200 );
		exit( 'OK' );
	}

	/**
	 * Verify a Stripe webhook signature.
	 */
	private static function verify_stripe_signature( $payload, $sig_header, $secret ) {
		if ( empty( $sig_header ) ) {
			return false;
		}

		$parts    = explode( ',', $sig_header );
		$timestamp = null;
		$signatures = array();

		foreach ( $parts as $part ) {
			$kv = explode( '=', trim( $part ), 2 );
			if ( 't' === $kv[0] ) {
				$timestamp = $kv[1];
			} elseif ( 'v1' === $kv[0] ) {
				$signatures[] = $kv[1];
			}
		}

		if ( null === $timestamp || empty( $signatures ) ) {
			return false;
		}

		$signed_payload = $timestamp . '.' . $payload;
		$expected       = hash_hmac( 'sha256', $signed_payload, $secret );

		foreach ( $signatures as $sig ) {
			if ( hash_equals( $expected, $sig ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get bank transfer details for display.
	 */
	public static function get_bank_transfer_details() {
		return array(
			'bank_name'    => get_option( 'inscience_bank_name', 'ANZ Bank' ),
			'account_name' => get_option( 'inscience_bank_account_name', 'InScience Ltd' ),
			'account_number' => get_option( 'inscience_bank_account_number', '' ),
			'reference'    => get_option( 'inscience_bank_reference_instructions', __( 'Please use your enrolment ID as the payment reference.', 'inscience-training' ) ),
		);
	}
}

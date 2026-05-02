<?php
/**
 * Enrolment handling – shortcode, form processing, DB writes.
 *
 * @package InScience_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InScience_Enrolment {

	/** @var InScience_Enrolment */
	private static $instance = null;

	/** Ethnic group options */
	const ETHNIC_GROUPS = array(
		'nz_european'       => 'NZ European / Pākehā',
		'maori'             => 'New Zealand Māori',
		'samoan'            => 'Samoan',
		'tongan'            => 'Tongan',
		'niuean'            => 'Niuean',
		'tokelauan'         => 'Tokelauan',
		'fijian'            => 'Fijian',
		'chinese'           => 'Chinese',
		'asian'             => 'Asian',
		'indian'            => 'Indian',
		'other_pacific'     => 'Other Pacific Island',
		'other'             => 'Other',
	);

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'inscience_enrolment_form', array( $this, 'render_form_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets() {
		global $post;
		// Only enqueue when shortcode is present.
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'inscience_enrolment_form' ) ) {
			wp_enqueue_style(
				'inscience-public',
				INSCIENCE_PLUGIN_URL . 'public/assets/css/inscience-public.css',
				array(),
				INSCIENCE_VERSION
			);
			wp_enqueue_script(
				'inscience-enrolment',
				INSCIENCE_PLUGIN_URL . 'public/assets/js/inscience-enrolment.js',
				array( 'jquery' ),
				INSCIENCE_VERSION,
				true
			);
			wp_localize_script( 'inscience-enrolment', 'inscienceEnrolment', array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'inscience_enrolment' ),
				'stripe_pk' => get_option( 'inscience_stripe_public_key', '' ),
			) );
		}
	}

	/**
	 * Shortcode: [inscience_enrolment_form course_id="123"]
	 * If course_id omitted, a dropdown of upcoming courses is shown.
	 */
	public function render_form_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'course_id' => 0 ), $atts, 'inscience_enrolment_form' );
		$course_id = absint( $atts['course_id'] );

		$courses = InScience_Course_CPT::get_upcoming_courses();

		// Success / error message after form submit redirect.
		$message = '';
		if ( isset( $_GET['inscience_status'] ) ) {
			if ( 'success' === sanitize_text_field( wp_unslash( $_GET['inscience_status'] ) ) ) {
				$message = '<div class="inscience-notice inscience-success">' . esc_html__( 'Thank you! Your enrolment has been received. You will receive a confirmation email shortly.', 'inscience-training' ) . '</div>';
			} elseif ( 'payment_complete' === sanitize_text_field( wp_unslash( $_GET['inscience_status'] ) ) ) {
				$message = '<div class="inscience-notice inscience-success">' . esc_html__( 'Payment received! Your enrolment is confirmed.', 'inscience-training' ) . '</div>';
			} elseif ( 'error' === sanitize_text_field( wp_unslash( $_GET['inscience_status'] ) ) ) {
				$error_msg = isset( $_GET['inscience_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['inscience_msg'] ) ) : __( 'An error occurred. Please try again.', 'inscience-training' );
				$message = '<div class="inscience-notice inscience-error">' . esc_html( $error_msg ) . '</div>';
			}
		}

		ob_start();
		echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		include INSCIENCE_PLUGIN_DIR . 'public/views/enrolment-form.php';
		return ob_get_clean();
	}

	/**
	 * Process enrolment form submission (called from InScience_Ajax).
	 *
	 * @return int|WP_Error Enrolment ID or WP_Error.
	 */
	public static function process_submission( $data ) {
		global $wpdb;

		// Validate required fields.
		$required = array(
			'course_id', 'enrolment_type', 'given_names', 'last_name',
			'street_address', 'city', 'postcode', 'email',
			'date_of_birth', 'phone', 'ethnic_group', 'gender',
			'payment_method', 'declaration',
		);

		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				// translators: %s is the field name.
				return new WP_Error( 'missing_field', sprintf( __( 'The field "%s" is required.', 'inscience-training' ), $field ) );
			}
		}

		if ( empty( $data['declaration'] ) ) {
			return new WP_Error( 'declaration', __( 'You must accept the declaration to proceed.', 'inscience-training' ) );
		}

		// Sanitize.
		$insert = array(
			'course_id'      => absint( $data['course_id'] ),
			'enrolment_type' => sanitize_text_field( $data['enrolment_type'] ),
			'employer'       => sanitize_text_field( $data['employer'] ?? '' ),
			'branch'         => sanitize_text_field( $data['branch'] ?? '' ),
			'group_email'    => sanitize_email( $data['group_email'] ?? '' ),
			'given_names'    => sanitize_text_field( $data['given_names'] ),
			'last_name'      => sanitize_text_field( $data['last_name'] ),
			'preferred_name' => sanitize_text_field( $data['preferred_name'] ?? '' ),
			'street_address' => sanitize_text_field( $data['street_address'] ),
			'city'           => sanitize_text_field( $data['city'] ),
			'postcode'       => sanitize_text_field( $data['postcode'] ),
			'email'          => sanitize_email( $data['email'] ),
			'date_of_birth'  => sanitize_text_field( $data['date_of_birth'] ),
			'phone'          => sanitize_text_field( $data['phone'] ),
			'ethnic_group'   => sanitize_text_field( is_array( $data['ethnic_group'] ) ? implode( ', ', $data['ethnic_group'] ) : $data['ethnic_group'] ),
			'gender'         => sanitize_text_field( $data['gender'] ),
			'payment_method' => sanitize_text_field( $data['payment_method'] ),
			'payment_status' => 'pending',
			'declaration'    => 1,
			'status'         => 'pending',
		);

		$result = $wpdb->insert( $wpdb->prefix . 'inscience_enrolments', $insert );

		if ( false === $result ) {
			return new WP_Error( 'db_error', __( 'Could not save enrolment. Please try again.', 'inscience-training' ) );
		}

		$enrolment_id = $wpdb->insert_id;

		// Send emails.
		InScience_Emails::send_enrolment_confirmation( $enrolment_id );
		InScience_Emails::send_admin_notification( $enrolment_id );

		return $enrolment_id;
	}

	/**
	 * Get enrolment by ID.
	 */
	public static function get_enrolment( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}inscience_enrolments WHERE id = %d",
			absint( $id )
		) );
	}

	/**
	 * Update enrolment status.
	 */
	public static function update_status( $id, $status ) {
		global $wpdb;
		return $wpdb->update(
			$wpdb->prefix . 'inscience_enrolments',
			array( 'status' => sanitize_text_field( $status ) ),
			array( 'id' => absint( $id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Update payment status.
	 */
	public static function update_payment_status( $id, $payment_status ) {
		global $wpdb;
		return $wpdb->update(
			$wpdb->prefix . 'inscience_enrolments',
			array( 'payment_status' => sanitize_text_field( $payment_status ) ),
			array( 'id' => absint( $id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}
}

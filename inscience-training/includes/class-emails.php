<?php
/**
 * Email sending and template management.
 *
 * @package InScience_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InScience_Emails {

	/** @var InScience_Emails */
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
	}

	public function set_html_content_type() {
		return 'text/html';
	}

	/**
	 * Get a template row by slug.
	 *
	 * @param string $slug
	 * @return object|null
	 */
	public static function get_template( $slug ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}inscience_email_templates WHERE slug = %s",
			$slug
		) );
	}

	/**
	 * Replace placeholders in subject / body.
	 *
	 * @param string $text
	 * @param array  $vars key => value
	 * @return string
	 */
	public static function replace_vars( $text, $vars ) {
		foreach ( $vars as $key => $value ) {
			$text = str_replace( '{' . $key . '}', $value, $text );
		}
		return $text;
	}

	/**
	 * Build an HTML email body by wrapping plain text in a simple template.
	 */
	private static function build_html( $body ) {
		$from_name  = get_option( 'inscience_email_from_name', get_bloginfo( 'name' ) );
		$logo_url   = get_option( 'inscience_email_logo', '' );
		$body_html  = nl2br( esc_html( $body ) );

		ob_start();
		?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:0;}
.wrapper{max-width:600px;margin:0 auto;background:#fff;padding:30px;}
.header{text-align:center;padding-bottom:20px;border-bottom:2px solid #003366;}
.header img{max-height:80px;}
.content{padding:20px 0;color:#333;line-height:1.6;}
.footer{margin-top:20px;padding-top:10px;border-top:1px solid #ccc;font-size:12px;color:#999;text-align:center;}
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <?php if ( $logo_url ) : ?>
    <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $from_name ); ?>">
    <?php else : ?>
    <h2><?php echo esc_html( $from_name ); ?></h2>
    <?php endif; ?>
  </div>
  <div class="content">
    <?php echo $body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
  </div>
  <div class="footer">
    &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $from_name ); ?>
  </div>
</div>
</body>
</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Send an email using a template.
	 *
	 * @param string $to
	 * @param string $slug   Template slug.
	 * @param array  $vars   Placeholder replacements.
	 * @return bool
	 */
	public static function send( $to, $slug, $vars ) {
		$template = self::get_template( $slug );
		if ( ! $template ) {
			return false;
		}

		$subject = self::replace_vars( $template->subject, $vars );
		$body    = self::replace_vars( $template->body, $vars );
		$html    = self::build_html( $body );

		$from_name  = get_option( 'inscience_email_from_name', get_bloginfo( 'name' ) );
		$from_email = get_option( 'inscience_email_from_address', get_option( 'admin_email' ) );
		$headers    = array( 'From: ' . $from_name . ' <' . $from_email . '>' );

		return wp_mail( $to, $subject, $html, $headers );
	}

	/**
	 * Send enrolment confirmation to attendee.
	 */
	public static function send_enrolment_confirmation( $enrolment_id ) {
		$enrolment = InScience_Enrolment::get_enrolment( $enrolment_id );
		if ( ! $enrolment ) {
			return false;
		}

		$course_meta  = InScience_Course_CPT::get_course_meta( $enrolment->course_id );
		$course_title = get_the_title( $enrolment->course_id );

		$payment_instructions = '';
		if ( 'bank_transfer' === $enrolment->payment_method ) {
			$bank = InScience_Payment::get_bank_transfer_details();
			$payment_instructions = sprintf(
				/* translators: %1$s bank name, %2$s account name, %3$s account number, %4$s reference instructions */
				__( "Please make payment to:\nBank: %1\$s\nAccount Name: %2\$s\nAccount Number: %3\$s\n%4\$s", 'inscience-training' ),
				$bank['bank_name'],
				$bank['account_name'],
				$bank['account_number'],
				$bank['reference']
			);
		} elseif ( 'on_account' === $enrolment->payment_method ) {
			$payment_instructions = __( 'An invoice will be sent to your account.', 'inscience-training' );
		} elseif ( 'stripe' === $enrolment->payment_method ) {
			$payment_instructions = __( 'Your credit card payment has been processed via Stripe.', 'inscience-training' );
		}

		$vars = array(
			'enrolment_id'         => $enrolment_id,
			'given_names'          => $enrolment->given_names,
			'last_name'            => $enrolment->last_name,
			'course_title'         => $course_title,
			'course_date'          => $course_meta['course_date'],
			'course_type'          => ucfirst( $course_meta['course_type'] ?? '' ),
			'course_location'      => $course_meta['course_city'] ? ( InScience_Course_CPT::NZ_CITIES[ $course_meta['course_city'] ] ?? ucfirst( $course_meta['course_city'] ) ) : ( $course_meta['course_location'] ?? '' ),
			'payment_method'       => ucwords( str_replace( '_', ' ', $enrolment->payment_method ) ),
			'payment_instructions' => $payment_instructions,
		);

		return self::send( $enrolment->email, 'enrolment_confirmation', $vars );
	}

	/**
	 * Send admin notification of new enrolment.
	 */
	public static function send_admin_notification( $enrolment_id ) {
		$enrolment = InScience_Enrolment::get_enrolment( $enrolment_id );
		if ( ! $enrolment ) {
			return false;
		}

		$course_meta  = InScience_Course_CPT::get_course_meta( $enrolment->course_id );
		$course_title = get_the_title( $enrolment->course_id );
		$admin_email  = get_option( 'inscience_admin_email', get_option( 'admin_email' ) );

		$vars = array(
			'enrolment_id'   => $enrolment_id,
			'given_names'    => $enrolment->given_names,
			'last_name'      => $enrolment->last_name,
			'email'          => $enrolment->email,
			'phone'          => $enrolment->phone,
			'employer'       => $enrolment->employer,
			'course_title'   => $course_title,
			'course_date'    => $course_meta['course_date'],
			'course_type'    => ucfirst( $course_meta['course_type'] ?? '' ),
			'course_location'=> $course_meta['course_city'] ? ( InScience_Course_CPT::NZ_CITIES[ $course_meta['course_city'] ] ?? ucfirst( $course_meta['course_city'] ) ) : ( $course_meta['course_location'] ?? '' ),
			'payment_method' => ucwords( str_replace( '_', ' ', $enrolment->payment_method ) ),
			'admin_url'      => admin_url( 'admin.php?page=inscience-enrolments&id=' . $enrolment_id ),
		);

		return self::send( $admin_email, 'enrolment_admin', $vars );
	}

	/**
	 * Send payment received confirmation.
	 */
	public static function send_payment_received( $enrolment_id ) {
		$enrolment = InScience_Enrolment::get_enrolment( $enrolment_id );
		if ( ! $enrolment ) {
			return false;
		}

		$course_meta  = InScience_Course_CPT::get_course_meta( $enrolment->course_id );
		$course_title = get_the_title( $enrolment->course_id );

		$vars = array(
			'enrolment_id' => $enrolment_id,
			'given_names'  => $enrolment->given_names,
			'last_name'    => $enrolment->last_name,
			'course_title' => $course_title,
			'course_date'  => $course_meta['course_date'],
		);

		return self::send( $enrolment->email, 'payment_received', $vars );
	}

	/**
	 * Send new course notification to all subscribers.
	 */
	public static function send_new_course_notifications( $course_id ) {
		$course_meta  = InScience_Course_CPT::get_course_meta( $course_id );
		$course_title = get_the_title( $course_id );

		$subscribers = InScience_Notification::get_subscribers( $course_meta['course_type'] ?? '' );
		if ( empty( $subscribers ) ) {
			return;
		}

		$enrol_url = get_permalink( get_option( 'inscience_enrolment_page_id' ) );

		foreach ( $subscribers as $subscriber ) {
			$vars = array(
				'name'            => $subscriber->name ?: $subscriber->email,
				'course_title'    => $course_title,
				'course_date'     => $course_meta['course_date'],
				'course_type'     => ucfirst( $course_meta['course_type'] ?? '' ),
				'course_location' => $course_meta['course_city'] ? ( InScience_Course_CPT::NZ_CITIES[ $course_meta['course_city'] ] ?? ucfirst( $course_meta['course_city'] ) ) : ( $course_meta['course_location'] ?? '' ),
				'enrol_url'       => add_query_arg( 'course_id', $course_id, $enrol_url ),
				'unsubscribe_url' => InScience_Notification::get_unsubscribe_url( $subscriber->email ),
			);
			self::send( $subscriber->email, 'new_course_notification', $vars );
		}
	}
}

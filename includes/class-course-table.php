<?php
/**
 * Course Table shortcode – outputs upcoming courses in an HTML table.
 *
 * Shortcode: [inscience_course_table]
 *
 * Attributes:
 *   type  – filter by delivery type: "classroom", "zoom", or "" (all). Default: "".
 *   limit – maximum number of rows. Default: 0 (all).
 *
 * @package InScience_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InScience_Course_Table {

	/** @var InScience_Course_Table */
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'inscience_course_table', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	public function register_assets() {
		wp_register_style(
			'inscience-public',
			INSCIENCE_PLUGIN_URL . 'public/assets/css/inscience-public.css',
			array(),
			INSCIENCE_VERSION
		);
	}

	/**
	 * Shortcode handler.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'type'  => '',
				'limit' => 0,
			),
			$atts,
			'inscience_course_table'
		);

		$filter_type = sanitize_text_field( $atts['type'] );
		$limit       = absint( $atts['limit'] );

		wp_enqueue_style( 'inscience-public' );

		$all_courses = InScience_Course_CPT::get_upcoming_courses();
		$courses     = array();

		foreach ( $all_courses as $course ) {
			if ( $filter_type && $course['type'] !== $filter_type ) {
				continue;
			}
			$courses[] = $course;
			if ( $limit > 0 && count( $courses ) >= $limit ) {
				break;
			}
		}

		$enrolment_page_id = get_option( 'inscience_enrolment_page_id' );

		ob_start();
		include INSCIENCE_PLUGIN_DIR . 'public/views/course-table.php';
		return ob_get_clean();
	}
}

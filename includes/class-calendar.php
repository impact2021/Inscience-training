<?php
/**
 * Calendar shortcode – outputs a FullCalendar-powered visual calendar of courses.
 *
 * Shortcode: [inscience_calendar]
 *
 * @package InScience_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InScience_Calendar {

	/** @var InScience_Calendar */
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'inscience_calendar', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	public function register_assets() {
		/*
		 * FullCalendar v6 – loaded from CDN.
		 * To serve locally instead, place fullcalendar.min.css and fullcalendar.min.js
		 * in /public/assets/css/ and /public/assets/js/ respectively, then
		 * swap the URLs to INSCIENCE_PLUGIN_URL . 'public/assets/…'.
		 */
		wp_register_style(
			'fullcalendar',
			'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css',
			array(),
			'6.1.11'
		);
		wp_register_script(
			'fullcalendar',
			'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js',
			array(),
			'6.1.11',
			true
		);
		wp_register_style(
			'inscience-public',
			INSCIENCE_PLUGIN_URL . 'public/assets/css/inscience-public.css',
			array( 'fullcalendar' ),
			INSCIENCE_VERSION
		);
		wp_register_script(
			'inscience-calendar',
			INSCIENCE_PLUGIN_URL . 'public/assets/js/inscience-calendar.js',
			array( 'fullcalendar' ),
			INSCIENCE_VERSION,
			true
		);
	}

	/**
	 * Shortcode handler.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'inline_form' => '0' ), $atts, 'inscience_calendar' );
		$inline_form = ! empty( $atts['inline_form'] ) && '0' !== $atts['inline_form'];

		wp_enqueue_style( 'fullcalendar' );
		wp_enqueue_style( 'inscience-public' );
		wp_enqueue_script( 'fullcalendar' );
		wp_enqueue_script( 'inscience-calendar' );

		if ( $inline_form ) {
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

		$courses = InScience_Course_CPT::get_upcoming_courses();
		$events  = array();

		$type_colors = array(
			'classroom' => '#003366',
			'zoom'      => '#00a651',
		);

		foreach ( $courses as $course ) {
			$title = '';
			if ( $course['type'] ) {
				$title .= strtoupper( $course['type'] ) . ': ';
			}
			$title .= $course['title'];

			$location = '';
			if ( 'classroom' === $course['type'] && $course['city'] ) {
				$location = InScience_Course_CPT::NZ_CITIES[ $course['city'] ] ?? ucfirst( $course['city'] );
			} elseif ( 'zoom' === $course['type'] ) {
				$location = 'Online (Zoom)';
			} elseif ( $course['location'] ) {
				$location = $course['location'];
			}

			$color      = $type_colors[ $course['type'] ] ?? '#555';
			$end_date   = $course['end_date'] ?: $course['date'];

			// FullCalendar end date is exclusive so add one day.
			if ( $end_date ) {
				$end_date = gmdate( 'Y-m-d', strtotime( $end_date . ' +1 day' ) );
			}

			$enrol_url = '';
			if ( ! $inline_form ) {
				$enrolment_page = get_option( 'inscience_enrolment_page_id' );
				if ( $enrolment_page ) {
					$enrol_url = add_query_arg( 'course_id', $course['id'], get_permalink( $enrolment_page ) );
				}
			}

			$events[] = array(
				'id'              => $course['id'],
				'title'           => $title,
				'start'           => $course['date'],
				'end'             => $end_date,
				'backgroundColor' => $color,
				'borderColor'     => $color,
				'extendedProps'   => array(
					'course_title_base' => $course['title'],
					'course_type'       => $course['type'],
					'us_codes'          => $course['us_codes'],
					'location'          => $location,
					'city'              => $course['city'],
					'start_time'        => $course['start_time'],
					'end_time'          => $course['end_time'],
					'end_date'          => $course['end_date'] ?: $course['date'],
					'price'             => number_format( (float) $course['price'], 2 ),
					'capacity'          => $course['capacity'],
					'status'            => $course['status'],
					'description'       => $course['description'],
					'enrol_url'         => $enrol_url,
				),
			);
		}

		// Build predefined course titles for the filter dropdown.
		$predefined_titles = json_decode( get_option( 'inscience_course_titles', '[]' ), true );
		if ( ! is_array( $predefined_titles ) ) {
			$predefined_titles = array();
		}

		wp_localize_script( 'inscience-calendar', 'inscienceCalendarData', array(
			'events'       => $events,
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'enrolPage'    => get_option( 'inscience_enrolment_page_id' ) ? get_permalink( get_option( 'inscience_enrolment_page_id' ) ) : '',
			'inlineForm'   => $inline_form,
			'courseTitles' => $predefined_titles,
		) );

		// If inline form, prepare variables for the enrolment form template.
		if ( $inline_form ) {
			$preset_course_id = isset( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : 0;
		}

		ob_start();
		include INSCIENCE_PLUGIN_DIR . 'public/views/calendar.php';
		return ob_get_clean();
	}
}

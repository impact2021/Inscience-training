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
		wp_enqueue_style( 'fullcalendar' );
		wp_enqueue_style( 'inscience-public' );
		wp_enqueue_script( 'fullcalendar' );
		wp_enqueue_script( 'inscience-calendar' );

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
			$enrolment_page = get_option( 'inscience_enrolment_page_id' );
			if ( $enrolment_page ) {
				$enrol_url = add_query_arg( 'course_id', $course['id'], get_permalink( $enrolment_page ) );
			}

			$events[] = array(
				'id'              => $course['id'],
				'title'           => $title,
				'start'           => $course['date'],
				'end'             => $end_date,
				'backgroundColor' => $color,
				'borderColor'     => $color,
				'extendedProps'   => array(
					'course_type'   => $course['type'],
					'us_codes'      => $course['us_codes'],
					'location'      => $location,
					'city'          => $course['city'],
					'time'          => $course['time'],
					'price'         => number_format( (float) $course['price'], 2 ),
					'capacity'      => $course['capacity'],
					'status'        => $course['status'],
					'description'   => $course['description'],
					'enrol_url'     => $enrol_url,
				),
			);
		}

		wp_localize_script( 'inscience-calendar', 'inscienceCalendarData', array(
			'events'           => $events,
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'enrolPage'        => get_option( 'inscience_enrolment_page_id' ) ? get_permalink( get_option( 'inscience_enrolment_page_id' ) ) : '',
			'notifyPage'       => get_option( 'inscience_notification_page_id' ) ? get_permalink( get_option( 'inscience_notification_page_id' ) ) : '',
			'notifyLabel'      => __( 'Get notified about upcoming courses', 'inscience-training' ),
			'notifySignUpText' => __( 'Sign Up', 'inscience-training' ),
		) );

		ob_start();
		include INSCIENCE_PLUGIN_DIR . 'public/views/calendar.php';
		return ob_get_clean();
	}
}

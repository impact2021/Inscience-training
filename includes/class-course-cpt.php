<?php
/**
 * Course Custom Post Type.
 *
 * @package InScience_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InScience_Course_CPT {

	/** @var InScience_Course_CPT */
	private static $instance = null;

	/** Course type options */
	const TYPES = array(
		'classroom' => 'Classroom',
		'zoom'      => 'Zoom',
	);

	/** NZ Cities */
	const NZ_CITIES = array(
		'auckland'    => 'Auckland',
		'wellington'  => 'Wellington',
		'christchurch'=> 'Christchurch',
		'hamilton'    => 'Hamilton',
		'tauranga'    => 'Tauranga',
		'dunedin'     => 'Dunedin',
		'palmerston_north' => 'Palmerston North',
		'napier'      => 'Napier',
		'nelson'      => 'Nelson',
		'rotorua'     => 'Rotorua',
		'other'       => 'Other',
	);

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_inscience_course', array( $this, 'save_meta' ), 10, 2 );
		add_filter( 'manage_inscience_course_posts_columns', array( $this, 'custom_columns' ) );
		add_action( 'manage_inscience_course_posts_custom_column', array( $this, 'custom_column_data' ), 10, 2 );
		add_filter( 'manage_edit-inscience_course_sortable_columns', array( $this, 'sortable_columns' ) );
	}

	public function register_cpt() {
		$labels = array(
			'name'               => __( 'Courses', 'inscience-training' ),
			'singular_name'      => __( 'Course', 'inscience-training' ),
			'add_new'            => __( 'Add New', 'inscience-training' ),
			'add_new_item'       => __( 'Add New Course', 'inscience-training' ),
			'edit_item'          => __( 'Edit Course', 'inscience-training' ),
			'new_item'           => __( 'New Course', 'inscience-training' ),
			'view_item'          => __( 'View Course', 'inscience-training' ),
			'search_items'       => __( 'Search Courses', 'inscience-training' ),
			'not_found'          => __( 'No courses found', 'inscience-training' ),
			'not_found_in_trash' => __( 'No courses found in Trash', 'inscience-training' ),
			'menu_name'          => __( 'Courses', 'inscience-training' ),
		);

		register_post_type( 'inscience_course', array(
			'labels'              => $labels,
			'public'              => true,
			'show_ui'             => false, // Managed via custom admin pages
			'show_in_menu'        => false,
			'show_in_rest'        => false,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
			'has_archive'         => false,
			'rewrite'             => array( 'slug' => 'courses' ),
		) );
	}

	public function register_taxonomy() {
		$labels = array(
			'name'          => __( 'Course Categories', 'inscience-training' ),
			'singular_name' => __( 'Course Category', 'inscience-training' ),
		);

		register_taxonomy( 'inscience_course_cat', 'inscience_course', array(
			'labels'       => $labels,
			'public'       => true,
			'hierarchical' => true,
			'rewrite'      => array( 'slug' => 'course-category' ),
			'show_ui'      => false,
		) );
	}

	public function add_meta_boxes() {
		add_meta_box(
			'inscience_course_details',
			__( 'Course Details', 'inscience-training' ),
			array( $this, 'render_meta_box' ),
			'inscience_course',
			'normal',
			'high'
		);
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'inscience_save_course', 'inscience_course_nonce' );
		$meta = self::get_course_meta( $post->ID );
		include INSCIENCE_PLUGIN_DIR . 'admin/views/meta-box-course.php';
	}

	public function save_meta( $post_id, $post ) {
		if ( ! isset( $_POST['inscience_course_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['inscience_course_nonce'] ) ), 'inscience_save_course' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'course_type'       => 'sanitize_text_field',
			'course_date'       => 'sanitize_text_field',
			'course_end_date'   => 'sanitize_text_field',
			'course_start_time' => 'sanitize_text_field',
			'course_end_time'   => 'sanitize_text_field',
			'course_location'   => 'sanitize_text_field',
			'course_city'       => 'sanitize_text_field',
			'course_capacity'   => 'absint',
			'course_price'      => 'floatval',
			'course_us_codes'   => 'sanitize_text_field',
			'course_status'     => 'sanitize_text_field',
		);

		foreach ( $fields as $key => $sanitize ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = call_user_func( $sanitize, wp_unslash( $_POST[ $key ] ) );
				update_post_meta( $post_id, '_' . $key, $value );
			}
		}
	}

	/**
	 * Get all meta for a course post.
	 *
	 * @param int $post_id
	 * @return array
	 */
	public static function get_course_meta( $post_id ) {
		$keys = array(
			'course_type', 'course_date', 'course_end_date', 'course_start_time', 'course_end_time',
			'course_location', 'course_city', 'course_capacity', 'course_price',
			'course_us_codes', 'course_status',
		);
		$meta = array();
		foreach ( $keys as $key ) {
			$meta[ $key ] = get_post_meta( $post_id, '_' . $key, true );
		}
		return $meta;
	}

	/**
	 * Get upcoming courses as an array suitable for JSON / calendar.
	 *
	 * @param array $args Optional WP_Query args.
	 * @return array
	 */
	public static function get_upcoming_courses( $args = array() ) {
		$defaults = array(
			'post_type'      => 'inscience_course',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_key'       => '_course_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_course_date',
					'value'   => gmdate( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
				array(
					'key'     => '_course_status',
					'value'   => 'cancelled',
					'compare' => '!=',
				),
			),
		);

		$query_args = wp_parse_args( $args, $defaults );
		$query      = new WP_Query( $query_args );
		$courses    = array();

		foreach ( $query->posts as $post ) {
			$meta = self::get_course_meta( $post->ID );
			$courses[] = array(
				'id'          => $post->ID,
				'title'       => get_the_title( $post ),
				'description' => wp_strip_all_tags( get_the_content( null, false, $post ) ),
				'type'        => $meta['course_type'],
				'date'        => $meta['course_date'],
				'end_date'    => $meta['course_end_date'],
				'start_time'  => $meta['course_start_time'],
				'end_time'    => $meta['course_end_time'],
				'location'    => $meta['course_location'],
				'city'        => $meta['course_city'],
				'capacity'    => (int) $meta['course_capacity'],
				'price'       => (float) $meta['course_price'],
				'us_codes'    => $meta['course_us_codes'],
				'status'      => $meta['course_status'] ?: 'open',
			);
		}

		return $courses;
	}

	public function custom_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['course_date'] = __( 'Date', 'inscience-training' );
				$new['course_type'] = __( 'Type', 'inscience-training' );
				$new['course_city'] = __( 'City', 'inscience-training' );
				$new['enrolments'] = __( 'Enrolments', 'inscience-training' );
			}
		}
		return $new;
	}

	public function custom_column_data( $column, $post_id ) {
		switch ( $column ) {
			case 'course_date':
				echo esc_html( get_post_meta( $post_id, '_course_date', true ) );
				break;
			case 'course_type':
				echo esc_html( ucfirst( get_post_meta( $post_id, '_course_type', true ) ) );
				break;
			case 'course_city':
				$city = get_post_meta( $post_id, '_course_city', true );
				echo esc_html( self::NZ_CITIES[ $city ] ?? ucfirst( $city ) );
				break;
			case 'enrolments':
				global $wpdb;
				$count = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}inscience_enrolments WHERE course_id = %d AND status != 'cancelled'",
					$post_id
				) );
				echo '<a href="' . esc_url( admin_url( 'admin.php?page=inscience-enrolments&course_id=' . $post_id ) ) . '">' . absint( $count ) . '</a>';
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['course_date'] = 'course_date';
		return $columns;
	}
}

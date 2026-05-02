<?php
/**
 * Plugin Name:       InScience Training
 * Plugin URI:        https://www.inscience.co.nz/
 * Description:       Manages course listings, enrolments, payments, and notifications for InScience Ltd.
 * Version:           1.1.2
 * Author:            InScience Ltd
 * Author URI:        https://www.inscience.co.nz/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       inscience-training
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'INSCIENCE_VERSION', '1.1.2' );
define( 'INSCIENCE_PLUGIN_FILE', __FILE__ );
define( 'INSCIENCE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'INSCIENCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'INSCIENCE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Autoload classes
spl_autoload_register( function ( $class ) {
	$prefix = 'InScience_';
	$base_dir = INSCIENCE_PLUGIN_DIR . 'includes/';

	if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
		return;
	}

	$relative = substr( $class, strlen( $prefix ) );
	$file = $base_dir . 'class-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

/**
 * Main plugin class.
 */
final class InScience_Training {

	/** @var InScience_Training */
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	private function includes() {
		require_once INSCIENCE_PLUGIN_DIR . 'includes/class-install.php';
		require_once INSCIENCE_PLUGIN_DIR . 'includes/class-course-cpt.php';
		require_once INSCIENCE_PLUGIN_DIR . 'includes/class-enrolment.php';
		require_once INSCIENCE_PLUGIN_DIR . 'includes/class-payment.php';
		require_once INSCIENCE_PLUGIN_DIR . 'includes/class-notification.php';
		require_once INSCIENCE_PLUGIN_DIR . 'includes/class-emails.php';
		require_once INSCIENCE_PLUGIN_DIR . 'includes/class-calendar.php';
		require_once INSCIENCE_PLUGIN_DIR . 'includes/class-ajax.php';

		if ( is_admin() ) {
			require_once INSCIENCE_PLUGIN_DIR . 'includes/class-admin.php';
		}
	}

	private function init_hooks() {
		register_activation_hook( INSCIENCE_PLUGIN_FILE, array( 'InScience_Install', 'activate' ) );
		register_deactivation_hook( INSCIENCE_PLUGIN_FILE, array( 'InScience_Install', 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain(
			'inscience-training',
			false,
			dirname( INSCIENCE_PLUGIN_BASENAME ) . '/languages/'
		);
	}

	public function init() {
		InScience_Course_CPT::instance();
		InScience_Enrolment::instance();
		InScience_Payment::instance();
		InScience_Notification::instance();
		InScience_Emails::instance();
		InScience_Calendar::instance();
		InScience_Ajax::instance();

		if ( is_admin() ) {
			InScience_Admin::instance();
		}
	}
}

/**
 * Returns the main instance of InScience_Training.
 */
function inscience_training() {
	return InScience_Training::instance();
}

// Kick off.
inscience_training();

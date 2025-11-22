<?php
/**
 * Plugin Name: Cookie Compliance Manager
 * Plugin URI: https://vishavjeet.in/cookie-compliance-manager
 * Description: Cookie Compliance Manager is a simple, lightweight plugin that helps your website comply with cookie consent regulations such as GDPR and CCPA. Display a customizable cookie banner to inform visitors about cookie usage and obtain their consent.
 * Version: 1.0.0
 * Author: Vishavjeet Choubey
 * Author URI: https://vishavjeet.in
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cookie-compliance-manager
 * Domain Path: /languages
 *
 * @package Cookie_Compliance_Manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'WCCM_VERSION', '1.0.0' );
define( 'WCCM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCCM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCCM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class.
 */
class WCCM_Plugin {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Get instance of this class.
	 *
	 * @return object Single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once WCCM_PLUGIN_DIR . 'includes/class-wccm-admin.php';
		require_once WCCM_PLUGIN_DIR . 'includes/class-wccm-frontend.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		if ( is_admin() ) {
			WCCM_Admin::get_instance();
		} else {
			WCCM_Frontend::get_instance();
		}
	}

	/**
	 * Activation hook.
	 */
	public static function activate() {
		// Set default options.
		$default_options = array(
			'enabled'          => '1',
			'message'          => __( 'We use cookies to ensure you get the best experience on our website.', 'cookie-compliance-manager' ),
			'accept_text'      => __( 'Accept', 'cookie-compliance-manager' ),
			'reject_text'      => __( 'Reject', 'cookie-compliance-manager' ),
			'position'         => 'bottom',
			'bg_color'         => '#2c3e50',
			'button_color'     => '#27ae60',
		);

		if ( ! get_option( 'wccm_settings' ) ) {
			add_option( 'wccm_settings', $default_options );
		}
	}


}

/**
 * Initialize the plugin.
 */
function wccm_init() {
	return WCCM_Plugin::get_instance();
}

// Start the plugin.
wccm_init();

// Register activation hook.
register_activation_hook( __FILE__, array( 'WCCM_Plugin', 'activate' ) );
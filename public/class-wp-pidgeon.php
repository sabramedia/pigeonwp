<?php
/**
 * WP Pidgeon
 *
 * @package   WP_Pidgeon
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://pigeonpaywall.com/
 * @copyright 2014 Sabramedia
 */

/**
 * The core class for the plugin
 *
 * @package WP_Pidgeon
 * @author  Your Name <email@example.com>
 */
class WP_Pidgeon {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'wp-pidgeon';

	/**
	 * Pidgeon settings returns from the API
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected $pidgeon_settings = array();

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// On each request, we need to make a call to Pidgeon
		add_action( 'init', array( $this, 'make_pidgeon_request' ) );

		// Include our custom functions
		require_once( plugin_dir_path( __FILE__ ) . 'public/includes/functions.php' );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {

		return $this->plugin_slug;

	}

	/**
	 * Return the pidgeon settings.
	 *
	 * @since    1.0.0
	 *
	 * @return    The pidgeon settings array.
	 */
	public function get_pidgeon_settings() {

		return $this->pidgeon_settings;

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Make a request to the Pidgeon Paywall
	 *
	 * @since    1.0.0
	 */
	public function make_pidgeon_request() {

		// Load the API class
		require_once( plugin_dir_path( __FILE__ ) . 'public/includes/class-pidgeon-api.php' );

		// Make the request
		$pidgeon_obj = new WP_Pidgeon;
		$this->pidgeon_settings = $pidgeon_obj->send();

	}

}

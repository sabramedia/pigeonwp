<?php
/**
 * WP Pigeon
 *
 * @package   WP_Pigeon
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://pigeonpaywall.com/
 * @copyright 2014 Sabramedia
 */

/**
 * The core class for the plugin
 *
 * @package WP_Pigeon
 * @author  Your Name <email@example.com>
 */
class WP_Pigeon {

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
	protected $plugin_slug = 'wp-pigeon';

	/**
	 * Pigeon values returns from the API
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected $pigeon_values = array();

	/**
	 * Pigeon settings defined by a user
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	protected $pigeon_settings = array();

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

		// On each request, we need to make a call to Pigeon
		add_action( 'init', array( $this, 'make_pigeon_request' ) );

		// Load functions
		add_action( 'init', array( $this, 'load_pigeon_functions' ) );

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
	 * Return the pigeon values.
	 *
	 * @since    1.0.0
	 *
	 * @return    The pigeon values array.
	 */
	public function get_pigeon_values() {

		return $this->pigeon_values;

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
	 * Load Pigeon Functions
	 *
	 * @since    1.0.0
	 */
	public function load_pigeon_functions() {

		// Include our custom functions
		require_once( plugin_dir_path( __FILE__ ) . 'public/includes/functions.php' );

	}

	/**
	 * Make a request to the Pigeon Paywall
	 *
	 * @since    1.0.0
	 */
	public function make_pigeon_request() {

		// Load the API class
		require_once( plugin_dir_path( __FILE__ ) . 'public/includes/class-pigeon-api.php' );

		// Get our content access settings
		$this->pigeon_settings['content_access'] = get_post_meta( get_the_ID(), '_wp_pidgeon_content_access', true );

		if ( ! $this->pigeon_settings['content_access'] )
			$this->pigeon_settings['content_access'] = 0;

		// Redirect setting
		$this->pigeon_settings['redirect'] = TRUE;

		// Subdomain
		$this->pigeon_settings['subdomain'] = 'my';

		// Secret key
		$this->pigeon_settings['secret'] = '';

		// Make the request
		$pigeon_api = new WP_Pigeon_Api;
		$this->pigeon_values = $pigeon_api->exec( $this->pigeon_settings );

	}

}

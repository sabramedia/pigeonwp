<?php
/**
 * WP Pigeon
 *
 * @package   WP_Pigeon
 * @author    Pigeon <support@pigeon.io>
 * @license   GPL-2.0+
 * @link      https://pigeon.io
 * @copyright 2014 Sabramedia
 */

/**
 * The core class for the plugin
 *
 * @package WP_Pigeon
 * @author  Pigeon <support@pigeon.io>
 */
class WP_Pigeon {
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.5.13';

	/**
	 * Unique identifier for the plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'wp-pigeon';

	/**
	 * Unique identifier for JS script.
	 *
	 * @since   1.6
	 *
	 * @var     string
	 */
	protected $js_handle = 'pigeon-js';

	/**
	 * Pigeon values returns from the API.
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	public $pigeon_values = array();

	/**
	 * Pigeon settings defined by a user.
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	public $pigeon_settings = array();

	/**
	 * Pigeon content access override from templates, takes precedence over admin interface setting control.
	 *
	 * @since    1.3.1
	 *
	 * @var      bool
	 */
	public $pigeon_content_access = null;

	/**
	 * Pigeon content price override from templates, takes precedence over admin interface setting control.
	 *
	 * @since    1.4.7
	 *
	 * @var      int
	 */
	public $pigeon_content_price = null;

	/**
	 * Pigeon content value override from templates, takes precedence over admin interface setting control.
	 *
	 * @since    1.4.0
	 *
	 * @var      int
	 */
	public $pigeon_content_value = null;

	/**
	 * Pigeon content prompt override from templates, takes precedence over admin interface setting control.
	 *
	 * @since    1.4.0
	 *
	 * @var      bool
	 */
	public $pigeon_content_prompt = null;

	/**
	 * Pigeon content ID is the WP post id.
	 *
	 * @since    1.4.0
	 *
	 * @var      int
	 */
	public $pigeon_content_id = null;

	/**
	 * Pigeon content title.
	 *
	 * @since    1.4.0
	 *
	 * @var      int
	 */
	public $pigeon_content_title = null;

	/**
	 * Pigeon content date.
	 *
	 * @since    1.4.7
	 *
	 * @var      string
	 */
	public $pigeon_content_date = null;

	/**
	 * Pigeon SDK Class.
	 *
	 * @since    1.5
	 *
	 * @var      string
	 */
	public $pigeon_sdk = null;


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
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Load SSO.
		add_action( 'init', array( $this, 'load_pigeon_sdk' ) );
		add_action( 'init', array( $this, 'load_sso' ) );

		// Load Shortcodes.
		add_action( 'init', array( $this, 'load_pigeon_shortcodes' ) );

		// On each request, we need to make a call to Pigeon.
		add_action( 'wp', array( $this, 'set_values' ) );

		// Load functions.
		add_action( 'wp', array( $this, 'load_pigeon_functions' ) );

		// Load JS.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
		if ( null === self::$instance ) {
			self::$instance = new self();
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
		load_plugin_textdomain( $domain, false, basename( plugin_dir_path( __DIR__ ) ) . '/languages/' );
	}

	/**
	 * Load the Pigeon Direct API SDK.
	 *
	 * @since    1.5.0
	 */
	public function load_pigeon_sdk() {
		$admin_options = get_option( 'wp_pigeon_settings' );

		if (
			isset( $admin_options['pigeon_api_secret_key'] ) && $admin_options['pigeon_api_secret_key'] &&
			isset( $admin_options['pigeon_api_user'] ) && $admin_options['pigeon_api_user']
		) {
			require_once plugin_dir_path( __FILE__ ) . '../sdk/Pigeon.php';

			Pigeon_Configuration::clientId( $admin_options['pigeon_api_user'] );
			Pigeon_Configuration::apiKey( $admin_options['pigeon_api_secret_key'] );
			Pigeon_Configuration::pigeonDomain( $admin_options['pigeon_subdomain'] );
			$this->pigeon_sdk = new Pigeon();

			// Load SSO here.
			add_action( 'init', array( $this, 'load_sso' ) );
		}
	}

	/**
	 * Load Pigeon Functions.
	 *
	 * @since    1.2.0
	 */
	public function load_pigeon_functions() {
		// Include our custom functions.
		require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
	}

	/**
	 * Initialize the JS.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! empty( $this->pigeon_settings['subdomain'] ) ) {
			wp_enqueue_script( $this->js_handle, '//' . $this->pigeon_settings['subdomain'] . '/c/assets/pigeon.js', array( 'jguery' ), '1.6', array( 'in_footer' => false ) );
		}

		$pigeon_session = md5( $this->pigeon_settings['subdomain'] );
		$http_host      = '';

		if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
			$http_host = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
		}

		$script = "
		var Pigeon = new PigeonClass({
			subdomain:'" . $this->pigeon_settings['subdomain'] . "',
			fingerprint:true," . (
				// If primary domain is not found in the subdomain, then use iFrame for SSO IDP.
				strstr( $this->pigeon_settings['subdomain'], str_replace( 'www.', '', $http_host ) ) === false ? "idp:true,\n\t\t\t" : ''
			) . '
			cid: null,
			cha: null
		});
		';

		switch ( $this->pigeon_settings['paywall_interrupt'] ) {
			case '1':
				$paywall_iterrupt = 1;
				break;
			case '2':
				$paywall_iterrupt = 0;
				break;
			case '3':
				$paywall_iterrupt = "'modal'";
				break;
		}

		$is_page_free = $this->pigeon_content_access ? $this->pigeon_content_access : $this->pigeon_settings['content_access'];

		// Don't count the 404 pages.
		if ( is_404() ) {
			$is_page_free = 1;
		}

		$script .= '
			Pigeon.paywall({
				redirect:' . $paywall_iterrupt . ',
				free:' . $is_page_free . ',
				contentId:' . ( $this->pigeon_content_id ? $this->pigeon_content_id : ( empty( $this->pigeon_settings['content_id'] ) ? 0 : $this->pigeon_settings['content_id'] ) ) . ",
				contentTitle:'" . rawurlencode( $this->pigeon_content_title ? $this->pigeon_content_title : ( empty( $this->pigeon_settings['content_title'] ) ? '' : $this->pigeon_settings['content_title'] ) ) . "',
				contentDate:'" . rawurlencode( $this->pigeon_content_date ? $this->pigeon_content_date : ( empty( $this->pigeon_settings['content_date'] ) ? '' : $this->pigeon_settings['content_date'] ) ) . "',
				contentPrice:" . ( $this->pigeon_content_price ? $this->pigeon_content_price : ( empty( $this->pigeon_settings['content_price'] ) ? 0 : preg_replace( '/([^0-9\.]+)/', '', $this->pigeon_settings['content_price'] ) ) ) . ',
				contentValue:' . ( $this->pigeon_content_value ? $this->pigeon_content_value : ( empty( $this->pigeon_settings['content_value'] ) ? 0 : $this->pigeon_settings['content_value'] ) ) . ',
				contentPrompt:' . ( $this->pigeon_content_prompt ? $this->pigeon_content_prompt : ( empty( $this->pigeon_settings['content_prompt'] ) ? 0 : $this->pigeon_settings['content_prompt'] ) ) . ",
				wpPostType: '" . ( isset( $this->pigeon_settings['wp_post_type'] ) && $this->pigeon_settings['wp_post_type'] ? $this->pigeon_settings['wp_post_type'] : '' ) . "'
			});

			Pigeon.widget.status();";

		wp_add_inline_script( $this->js_handle, $script, 'before' );
	}

	/**
	 * Load Pigeon Shortcodes.
	 *
	 * @since    1.4.8
	 */
	public function load_pigeon_shortcodes() {
		// Turns off warnings for loadHTML.
		libxml_use_internal_errors( true );

		function pigeon_protect_shortcode( $atts = array(), $content = null ) {
			// Run shortcode parser recursively.
			$content = do_shortcode( $content );
			$content = '<div class="pigeon-remove">' . $content . '</div><div class="pigeon-context-promotion" style="display:none;"><p>This page is available to subscribers. <a href="#" class="pigeon-open">Click here to sign in or get access</a>.</p></div>';

			return apply_filters( 'the_content', $content );
		}
		add_shortcode( 'pigeon_protect', 'pigeon_protect_shortcode' );

		// Pigeon display block and attribute conditions.
		function pigeon_display_shortcode( $atts = array(), $content = null, $tag = '' ) {
			// Normalize attribute keys, lowercase.
			$atts = array_change_key_case( (array) $atts, CASE_LOWER );

			// Override default attributes with user attributes.
			$pigeon_atts = shortcode_atts(
				array(
					'access' => 'disabled',
				),
				$atts,
				$tag
			);

			// Handle shortcode differently base on the paywall mode.
			$o = '';

			// Develop attr string.
			$attr_str = '';

			foreach ( $pigeon_atts as $key => $val ) {
				$attr_str .= ' data-' . $key . '="' . $val . '"';
			}

			// Run shortcode parser recursively.
			// Handle display conditions from the js plugin.
			$o .= '<div class="pigeon-message" style="display:none;"' . $attr_str . '>';
			if ( ! is_null( $content ) ) {
				// Run shortcode parser recursively.
				$content = do_shortcode( $content );

				// Secure output by executing the_content filter hook on $content.
				$o .= apply_filters( 'the_content', $content );
			}
			$o .= '</div>';

			return $o;
		}
		add_shortcode( 'pigeon_display_when', 'pigeon_display_shortcode' );

		function pigeon_content_expires_shortcode( $atts = array(), $content = null, $tag = '' ) {
			// Normalize attribute keys, lowercase.
			$atts = array_change_key_case( (array) $atts, CASE_LOWER );

			$pigeon_atts = shortcode_atts(
				array(
					'format' => 'F j, Y g:i A T',
				),
				$atts,
				$tag
			);

			// Run shortcode parser recursively.
			$content = '<div class="pigeon-content-expires" data-format="' . $pigeon_atts['format'] . '"></div>';

			return $content;
		}
		add_shortcode( 'pigeon_content_expires', 'pigeon_content_expires_shortcode' );
	}

	/**
	 * Set content values.
	 *
	 * @since    1.6
	 */
	public function set_values() {
		if ( is_admin() ) {
			return;
		}

		// Avoid asset requests.
		foreach ( array( '.css', '.js', '.woff', '.eot', '.ttf', '.svg', '.png', '.jpg', '.gif', '.cur', 'css?' ) as $asset ) {
			if ( strpos( basename( $_SERVER['REQUEST_URI'] ), $asset ) !== false ) {
				return;
			}
		}

		$admin_options = get_option( 'wp_pigeon_settings' );

		if ( array_key_exists( 'sucuriscan', $_GET ) ) {
			echo esc_html( '<!--sucuriscan-->' );
		}

		// Get our content access settings.
		if ( is_singular() ) {
			global $post;

			$this->pigeon_settings['content_id']     = $post->ID;
			$this->pigeon_settings['content_title']  = $post->post_title;
			$this->pigeon_settings['content_date']   = $post->post_date_gmt;
			$this->pigeon_settings['content_access'] = get_post_meta( $post->ID, '_wp_pigeon_content_access', true );
			$this->pigeon_settings['wp_post_type']   = $post->post_type;

			// Send zero dollar if the value meter is disabled.
			$this->pigeon_settings['content_price'] = isset( $admin_options['pigeon_content_value_pricing'] ) && $admin_options['pigeon_content_value_pricing'] == 1 ? get_post_meta( $post->ID, '_wp_pigeon_content_price', true ) : 0;

			// Send zero value if the value meter is disabled.
			$this->pigeon_settings['content_value']  = $admin_options['pigeon_content_value_meter'] == 1 ? (int) get_post_meta( $post->ID, '_wp_pigeon_content_value', true ) : 0;
			$this->pigeon_settings['content_prompt'] = (int) get_post_meta( $post->ID, '_wp_pigeon_content_prompt', true );
		}

		if ( ! isset( $this->pigeon_settings['content_access'] ) || $this->pigeon_settings['content_access'] == '' ) {
			$this->pigeon_settings['content_access'] = 0;
		}

		if ( ! isset( $this->pigeon_settings['content_value'] ) || $this->pigeon_settings['content_access'] == '' ) {
			$this->pigeon_settings['content_access'] = 0;
		}

		// Redirect setting (this could be already set via our functions).
		$this->pigeon_settings['redirect'] = isset( $admin_options['pigeon_paywall_interrupt'] ) && $admin_options['pigeon_paywall_interrupt'] ? ( isset( $admin_options['pigeon_paywall_interrupt'] ) && $admin_options['pigeon_paywall_interrupt'] == 1 ? true : false ) : true;

		// Paywall interrupt method.
		$this->pigeon_settings['paywall_interrupt'] = isset( $admin_options['pigeon_paywall_interrupt'] ) ? $admin_options['pigeon_paywall_interrupt'] : 3;

		// Subdomain.
		$this->pigeon_settings['subdomain'] = isset( $admin_options['pigeon_subdomain'] ) && $admin_options['pigeon_subdomain'] ? str_replace( array( 'https://', 'http://' ), '', $admin_options['pigeon_subdomain'] ) : 'my.' . str_replace( 'www.', '', $_SERVER['HTTP_HOST'] );

		// User.
		$this->pigeon_settings['user'] = isset( $admin_options['pigeon_api_user'] ) ? $admin_options['pigeon_api_user'] : '';

		// Secret key.
		$this->pigeon_settings['secret'] = isset( $admin_options['pigeon_api_secret_key'] ) ? $admin_options['pigeon_api_secret_key'] : '';

		$this->pigeon_values = $this->pigeon_settings;
	}
}

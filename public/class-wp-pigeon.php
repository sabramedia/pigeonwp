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
	const VERSION = '1.3.0';

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
	public $pigeon_values = array();

	/**
	 * Pigeon settings defined by a user
	 *
	 * @since    1.0.0
	 *
	 * @var      array
	 */
	public $pigeon_settings = array();

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
		add_action( 'wp', array( $this, 'make_pigeon_request' ) );

		// Load functions
		add_action( 'wp', array( $this, 'load_pigeon_functions' ) );

		// Load JS
		add_action( 'wp', array( $this, 'load_pigeon_js' ) );

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
	 * @since    1.2.0
	 */
	public function load_pigeon_functions() {

		// Include our custom functions
		require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );

	}

	public function init_pigeon_js()
	{
		echo '<script type="text/javascript">';

		echo "
		if( typeof Pigeon !== 'function' ){ window.location.href = 'http://".$this->pigeon_settings['subdomain']."/no-script'; }
		var Pigeon = new Pigeon({
			subdomain:'".$this->pigeon_settings['subdomain']."',
			apiUser:'".$this->pigeon_settings['user']."',
			apiSecret:'".$this->pigeon_settings['secret']."',
			fingerprint:true
		});

		Pigeon.widget.promotionDialog();
		";

		// Server Side plugin
		if( $this->pigeon_settings["paywall"] == 1 ){
			if( $this->pigeon_settings["soundcloud"] && ! $this->pigeon_values["user_status"]  ){
			echo "
			$(document).ready(function(){
				$('iframe').not('.pigeon-free').each(function(i,el){
					if( el.src.search('soundcloud.com') != -1 ){
						var widget = SC.Widget(el);
						widget.bind(SC.Widget.Events.PLAY,function(){
							this.pause();
							Pigeon.widget.promotionDialog('open');
						});
					}
				});
			});
			";
			}
		}

		// JS Plugin
		if( $this->pigeon_settings["paywall"] == 2 ){
			switch($this->pigeon_settings['paywall_interrupt']){
				case "1": $paywall_iterrupt = 1; break;
				case "2": $paywall_iterrupt = 0; break;
				case "3": $paywall_iterrupt = "'modal'"; break;
			}

			echo "
				Pigeon.paywall({
					redirect:".$paywall_iterrupt.",
					free:".$this->pigeon_settings['content_access']."
				});";

			if( $this->pigeon_settings["soundcloud"] ){
				echo "
				$(document).ready(function(){
					Pigeon.paywallPromise.done(function(data){

						if( ! data.user_status ){
							$('iframe').not('.pigeon-free').each(function(i,el){
								if( el.src.search('soundcloud.com') != -1 ){
									var widget = SC.Widget(el);
									widget.bind(SC.Widget.Events.PLAY,function(){
										this.pause();
										Pigeon.widget.promotionDialog('open');
									});
								}
							});
						}
					});
				});
				";
			}
		}



		echo '
		</script>
		';
	}

	/**
	 * Load Pigeon JS
	 *
	 * @since    1.2.0
	 */
	public function load_pigeon_js() {

		wp_enqueue_script("pigeon", "//".$this->pigeon_settings['subdomain']."/c/assets/pigeon-1.4.min.js",array("jquery"), self::VERSION );

		if( $this->pigeon_settings["soundcloud"] ){
			wp_enqueue_script("soundcloud", "//w.soundcloud.com/player/api.js",array("pigeon"), self::VERSION);
		}

		add_action("wp_head", array($this, 'init_pigeon_js') );
	}

	/**
	 * Make a request to the Pigeon Paywall
	 *
	 * @since    1.0.0
	 */
	public function make_pigeon_request() {
		$admin_options = get_option( 'wp_pigeon_settings' );

		if ( is_admin() )
			return;

		// Load the API class
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-pigeon-api.php' );

		// Get our content access settings
		if ( is_singular() ) {
			global $post;
			$this->pigeon_settings['content_access'] = get_post_meta( $post->ID, '_wp_pigeon_content_access', true );
		}

		if ( ! isset( $this->pigeon_settings['content_access'] ) || $this->pigeon_settings['content_access'] == '' )
			$this->pigeon_settings['content_access'] = 0;

		// Redirect setting (this could be already set via our functions)
		$this->pigeon_settings['redirect'] = $admin_options["pigeon_paywall_interrupt"] ? ( $admin_options["pigeon_paywall_interrupt"] == 1 ? TRUE : FALSE ) : TRUE;

		// Set Soundcloud option
		$this->pigeon_settings['soundcloud'] = $admin_options["pigeon_soundcloud"] ? ( $admin_options["pigeon_soundcloud"] == 1 ? TRUE : FALSE ) : TRUE;

		// Paywall implementation
		$this->pigeon_settings['paywall'] = $admin_options["pigeon_paywall"];

		// Paywall interrupt method
		$this->pigeon_settings['paywall_interrupt'] = $admin_options["pigeon_paywall_interrupt"];

		// Subdomain
		$this->pigeon_settings['subdomain'] = $admin_options["pigeon_subdomain"] ? str_replace(array("https://","http://"),"",$admin_options["pigeon_subdomain"]): 'my.' . str_replace( 'www.', '', $_SERVER["HTTP_HOST"] );

		// User
		$this->pigeon_settings['user'] = $admin_options["pigeon_api_user"];

		// Secret key
		$this->pigeon_settings['secret'] = $admin_options["pigeon_api_secret_key"];


		// Make the request
		if( $this->pigeon_settings['paywall'] == 1 ){
			$pigeon_api = new WP_Pigeon_Api;
			$this->pigeon_values = $pigeon_api->exec( $this->pigeon_settings );
		}
	}

}

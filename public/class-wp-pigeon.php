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
	const VERSION = '1.4.4';

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
	 * Pigeon content access override from templates, takes precedence over admin interface setting control.
	 *
	 * @since    1.3.1
	 *
	 * @var      bool
	 */
	public $pigeon_content_access = NULL;

	/**
	 * Pigeon content value override from templates, takes precedence over admin interface setting control.
	 *
	 * @since    1.4.0
	 *
	 * @var      int
	 */
	public $pigeon_content_value = NULL;

	/**
	 * Pigeon content prompt override from templates, takes precedence over admin interface setting control.
	 *
	 * @since    1.4.0
	 *
	 * @var      bool
	 */
	public $pigeon_content_prompt = NULL;

	/**
	 * Pigeon content ID is the WP post id.
	 *
	 * @since    1.4.0
	 *
	 * @var      int
	 */
	public $pigeon_content_id = NULL;

	/**
	 * Pigeon content title
	 *
	 * @since    1.4.0
	 *
	 * @var      int
	 */
	public $pigeon_content_title = NULL;


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

		// TODO Removed the PigeonClass check... this was done for adBlockers, but seems to be causing an issue with Bing.
		//if( typeof PigeonClass !== 'function' ){ window.location.href = 'http://".$this->pigeon_settings['subdomain']."/no-script'; }
		echo "
		var Pigeon = new PigeonClass({
			subdomain:'".$this->pigeon_settings['subdomain']."',
			apiUser:'".$this->pigeon_settings['user']."',
			apiSecret:'".$this->pigeon_settings['secret']."',
			fingerprint:true
		});
		";

		// Server Side plugin
		if( $this->pigeon_settings["paywall"] == 1 ){

			// Simulate a response promise here so the status widget will still work in server mode
			echo "
				var pdfd = jQuery.Deferred();
				var response = ".json_encode($this->pigeon_values).";
				pdfd.resolve(response);
				Pigeon.paywallPromise = pdfd.promise();
				Pigeon.widget.status();";

			// If the modal is set then pop it up
			if( !$this->pigeon_values["allowed"] && $this->pigeon_settings['paywall_interrupt'] == "3" ){
				if( $this->pigeon_settings['content_value'] ){
					echo "Pigeon.widget.promotionDialog('open',{
						content_id:'".$this->pigeon_settings['content_id']."',
						content_title:'".$this->pigeon_settings['content_title']."',
						content_value:'".$this->pigeon_settings['content_value']."'
					});";
				}else{
					echo "Pigeon.widget.promotionDialog('open');";
				}
			}

			if( $this->pigeon_settings["soundcloud"] && ! $this->pigeon_values["user_status"]  ){
			echo "
			jQuery(document).ready(function(){
				jQuery('iframe').not('.pigeon-free').each(function(i,el){
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
					free:".($this->pigeon_content_access ? $this->pigeon_content_access : $this->pigeon_settings['content_access']).",
					contentId:".($this->pigeon_content_id ? $this->pigeon_content_id : empty($this->pigeon_settings['content_id']) ? 0 : $this->pigeon_settings['content_id']).",
					contentTitle:'".($this->pigeon_content_title ? $this->pigeon_content_title : empty($this->pigeon_settings['content_title']) ? "" : $this->pigeon_settings['content_title'] )."',
					contentValue:".($this->pigeon_content_value ? $this->pigeon_content_value : empty($this->pigeon_settings['content_value']) ? 0 : $this->pigeon_settings['content_value']).",
					contentPrompt:".($this->pigeon_content_prompt ? $this->pigeon_content_prompt : empty($this->pigeon_settings['content_prompt']) ? 0 : $this->pigeon_settings['content_prompt'])."
				});

				Pigeon.widget.status();";

			if( $this->pigeon_settings["soundcloud"] ){
				echo "
				jQuery(document).ready(function(){
					Pigeon.paywallPromise.done(function(data){

						if( ! data.user_status ){
							jQueryg('iframe').not('.pigeon-free').each(function(i,el){
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

		// TODO removed based on the note above
		// If the JS paywall or modal interrupt is on then force script to be loaded
//		if( $this->pigeon_settings["paywall"] == 2 || $this->pigeon_settings['paywall_interrupt'] == "3" ){
//			echo '
//			<noscript>
//				<meta http-equiv="refresh" content="0; url=http://'.$this->pigeon_settings['subdomain'].'/no-script" />
//			</noscript>
//			';
//		}
	}

	/**
	 * Load Pigeon JS
	 *
	 * @since    1.2.0
	 */
	public function load_pigeon_js() {

		if( isset($this->pigeon_settings['subdomain']) )
			wp_enqueue_script("pigeon", "//".$this->pigeon_settings['subdomain']."/c/assets/pigeon-1.4.2.min.js",array("jquery"), self::VERSION );

		if( isset($this->pigeon_settings["soundcloud"] ) && $this->pigeon_settings["soundcloud"] ){
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

			$this->pigeon_settings['content_id'] = $post->ID;
			$this->pigeon_settings['content_title'] = $post->post_title;
			$this->pigeon_settings['content_access'] = get_post_meta( $post->ID, '_wp_pigeon_content_access', true );

			// Send zero value if the value meter is disabled
			$this->pigeon_settings['content_value'] = $admin_options["pigeon_content_value_meter"] == 1 ? (int)get_post_meta( $post->ID, '_wp_pigeon_content_value', true ) : 0;
			$this->pigeon_settings['content_prompt'] = (int)get_post_meta( $post->ID, '_wp_pigeon_content_prompt', true );
		}

		if ( ! isset( $this->pigeon_settings['content_access'] ) || $this->pigeon_settings['content_access'] == '' )
			$this->pigeon_settings['content_access'] = 0;

		if ( ! isset( $this->pigeon_settings['content_value'] ) || $this->pigeon_settings['content_access'] == '' )
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
		}else{
			$this->pigeon_values = $this->pigeon_settings;
		}
	}

}

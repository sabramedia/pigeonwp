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
	const VERSION = '1.4.8';

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
	 * Pigeon content price override from templates, takes precedence over admin interface setting control.
	 *
	 * @since    1.4.7
	 *
	 * @var      int
	 */
	public $pigeon_content_price = NULL;

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
	 * Pigeon content date
	 *
	 * @since    1.4.7
	 *
	 * @var      string
	 */
	public $pigeon_content_date = NULL;


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

		// Load Shortcodes
		add_action( 'init', array( $this, 'load_pigeon_shortcodes' ) );

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
		$pigeon_session = md5( $this->pigeon_settings['subdomain'] );
		echo "
		var Pigeon = new PigeonClass({
			subdomain:'".$this->pigeon_settings['subdomain']."',
			apiUser:'".$this->pigeon_settings['user']."',
			apiSecret:'".$this->pigeon_settings['secret']."',
			fingerprint:false,
			cid: ".( array_key_exists( $pigeon_session . "_id", $_COOKIE ) ? $_COOKIE[$pigeon_session . "_id"] : "null" ) .",
			cha: ".( array_key_exists( $pigeon_session . "_hash", $_COOKIE ) ? "'".$_COOKIE[$pigeon_session . "_hash"]."'" : "null" ) ."
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
				if( $this->pigeon_settings['content_value'] || $this->pigeon_settings['content_price'] ){
					echo "Pigeon.widget.promotionDialog('open',{
						content_id:'".$this->pigeon_settings['content_id']."',
						content_title:'".urlencode($this->pigeon_settings['content_title'])."',
						content_date:'".urlencode($this->pigeon_settings['content_date'])."',
						content_price:'".$this->pigeon_settings['content_price']."',
						content_value:'".$this->pigeon_settings['content_value']."'
					});";
				}else{
					echo "Pigeon.widget.promotionDialog('open');";
				}
			}

			if( $this->pigeon_settings["soundcloud"] &&
					(
					! $this->pigeon_values["user_status"] ||
					// Check user status for pages that aren't paywalled
					($this->pigeon_values["profile"] && !$this->pigeon_values["profile"]["status"])
					)
			){
			echo "
			jQuery(document).ready(function(){
				jQuery('iframe').not('.pigeon-free').each(function(i,el){
					if( el.src.search('soundcloud.com') != -1 ){
						jQuery(el).attr('sandbox','allow-same-origin allow-scripts');
						var iWidth = jQuery(el).width();
						var iHeight = jQuery(el).height();
						var shield = $('<div style=\"width:'+iWidth+'px; height:'+iHeight+'px; margin-top: -'+iHeight+'px; position:relative;\"></div>');
						shield.click(function(){
							Pigeon.widget.promotionDialog('open', ".($this->pigeon_values["profile"] ? "{'route_user_account':1}" : "null" ).");
						});
						jQuery(el).after(shield);
						var widget = SC.Widget(el);

						widget.bind(SC.Widget.Events.PLAY,function(){
							this.pause();
							// if logged in then load the user account page
							Pigeon.widget.promotionDialog('open', ".($this->pigeon_values["profile"] ? "{'route_user_account':1}" : "null" ).");
						});

						widget.unbind(SC.Widget.Events.CLICK_DOWNLOAD);
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
					contentTitle:'".urlencode($this->pigeon_content_title ? $this->pigeon_content_title : empty($this->pigeon_settings['content_title']) ? "" : $this->pigeon_settings['content_title'] )."',
					contentDate:'".urlencode($this->pigeon_content_date ? $this->pigeon_content_date : empty($this->pigeon_settings['content_date']) ? "" : $this->pigeon_settings['content_date'] )."',
					contentPrice:".($this->pigeon_content_price ? $this->pigeon_content_price : empty($this->pigeon_settings['content_price']) ? 0 : preg_replace("/([^0-9\.]+)/","",$this->pigeon_settings['content_price'])).",
					contentValue:".($this->pigeon_content_value ? $this->pigeon_content_value : empty($this->pigeon_settings['content_value']) ? 0 : $this->pigeon_settings['content_value']).",
					contentPrompt:".($this->pigeon_content_prompt ? $this->pigeon_content_prompt : empty($this->pigeon_settings['content_prompt']) ? 0 : $this->pigeon_settings['content_prompt'])."
				});

				Pigeon.widget.status();";

			if( $this->pigeon_settings["soundcloud"] ){
				echo "
				jQuery(document).ready(function(){
					Pigeon.paywallPromise.done(function(data){

						if( ! data.user_status || (data.profile && !data.profile.status)){
							jQuery('iframe').not('.pigeon-free').each(function(i,el){
								if( el.src.search('soundcloud.com') != -1 ){
									var widget = SC.Widget(el);
									widget.bind(SC.Widget.Events.PLAY,function(){
										this.pause();
										// if logged in then load the user account page
										Pigeon.widget.promotionDialog('open', (data.profile ? {'route_user_account':1} : null));
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
			wp_enqueue_script("pigeon", "//".$this->pigeon_settings['subdomain']."/c/assets/pigeon-1.5.min.js",array("jquery"), self::VERSION );

		if( isset($this->pigeon_settings["soundcloud"] ) && $this->pigeon_settings["soundcloud"] ){
			wp_enqueue_script("soundcloud", "//w.soundcloud.com/player/api.js",array("pigeon"), self::VERSION);
		}

		add_action("wp_head", array($this, 'init_pigeon_js') );
	}

	/**
	 * Load Pigeon Shortcodes
	 *
	 * @since    1.4.8
	 */

	public function load_pigeon_shortcodes()
	{
		function pigeon_protect_shortcode($atts=[], $content=null)
		{
			$pigeon_obj = WP_Pigeon::get_instance();
			// Handle shortcode differently base on the paywall mode

			// Server Side plugin
			if( $pigeon_obj->pigeon_settings["paywall"] == 1 ){
				if( ! $pigeon_obj->pigeon_values["allowed"] ){
					$content = "";
				}else{
					// run shortcode parser recursively
					$content = do_shortcode($content);
				}
			}

			// JS plugin
			if( $pigeon_obj->pigeon_settings["paywall"] == 2 ){

				// run shortcode parser recursively
				$content = do_shortcode($content);
				$content = '<div class="pigeon-blur">'.$content.'</div>';
			}

			return $content;
		}

		add_shortcode('pigeon_protect', 'pigeon_protect_shortcode');

		// Pigeon display block and attribute conditions

		function pigeon_display_shortcode($atts=[], $content=null, $tag='')
		{
			$pigeon_obj = WP_Pigeon::get_instance();

			// normalize attribute keys, lowercase
			$atts = array_change_key_case((array)$atts, CASE_LOWER);

			// override default attributes with user attributes
			$pigeon_atts = shortcode_atts([
							 'access' => 'disabled',
						 ], $atts, $tag);

			// Handle shortcode differently base on the paywall mode


			$o = '';

			// Server Side plugin
			if( $pigeon_obj->pigeon_settings["paywall"] == 1 ){

				$display_content = FALSE;
				foreach($pigeon_atts as $key=>$val){
					switch($key){
						case 'access':
							$user_allowed = $pigeon_obj->pigeon_values["allowed"];
							if( !$user_allowed && $val == "disabled" ){
								$display_content = TRUE;
							}elseif( $user_allowed && $val == "enabled"){
								$display_content = TRUE;
							}

							break;
					}
				}

				if( $display_content ){
					// run shortcode parser recursively
					// enclosing tags
					if (!is_null($content)) {
						// run shortcode parser recursively
						$content = do_shortcode($content);

						// secure output by executing the_content filter hook on $content
						$o .= apply_filters('the_content', $content);
					}
				}
			}

			// JS plugin
			if( $pigeon_obj->pigeon_settings["paywall"] == 2 ){
				// Develop attr string
				$attr_str = '';

				foreach($pigeon_atts as $key=>$val){
					$attr_str .= ' data-'.$key.'="'.$val.'"';
				}

				// run shortcode parser recursivel
				// Handle display conditions form the js plugin
				$o .= '<div class="pigeon-message" style="display:none;"'.$attr_str.'>';
				if (!is_null($content)) {
					// run shortcode parser recursively
					$content = do_shortcode($content);

					// secure output by executing the_content filter hook on $content
					$o .= apply_filters('the_content', $content);
				}
				$o .= '</div>';
			}

			return $o;
		}

		add_shortcode('pigeon_display_when', 'pigeon_display_shortcode');
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
			$this->pigeon_settings['content_date'] = $post->post_date_gmt;
			$this->pigeon_settings['content_access'] = get_post_meta( $post->ID, '_wp_pigeon_content_access', true );

			// Send zero dollar if the value meter is disabled
			$this->pigeon_settings['content_price'] = $admin_options["pigeon_content_value_pricing"] == 1 ? get_post_meta( $post->ID, '_wp_pigeon_content_price', true ) : 0;

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

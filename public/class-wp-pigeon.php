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
	const VERSION = '1.5.8';

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
	 * Pigeon SDK Class
	 *
	 * @since    1.5
	 *
	 * @var      string
	 */
	public $pigeon_sdk = NULL;


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

		// Load SSO
		add_action( 'init', array( $this, 'load_pigeon_sdk' ) );
		add_action( 'init', array( $this, 'load_sso' ) );

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
	 * Load the Pigeon Direct API SDK
	 *
	 * @since    1.5.0
	 */
	public function load_pigeon_sdk()
	{
		$admin_options = get_option( 'wp_pigeon_settings' );
		if( $admin_options['pigeon_api_secret_key'] && $admin_options['pigeon_api_user'] ){
			require_once( plugin_dir_path( __FILE__ ). "../sdk/Pigeon.php");

			Pigeon_Configuration::clientId($admin_options['pigeon_api_user']);
			Pigeon_Configuration::apiKey($admin_options['pigeon_api_secret_key']);
			Pigeon_Configuration::pigeonDomain($admin_options['pigeon_subdomain']);
			$this->pigeon_sdk = new Pigeon();

			// Load SSO here
			add_action( 'init', array( $this, 'load_sso' ) );
		}
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
		if( isset($this->pigeon_settings['subdomain']) )
			echo '<script type="text/javascript" src="//'.$this->pigeon_settings['subdomain'].'/c/assets/pigeon.js"></script>';

		echo '<script type="text/javascript">';

		// TODO Removed the PigeonClass check... this was done for adBlockers, but seems to be causing an issue with Bing.
		//if( typeof PigeonClass !== 'function' ){ window.location.href = 'http://".$this->pigeon_settings['subdomain']."/no-script'; }
		$pigeon_session = md5( $this->pigeon_settings['subdomain'] );
		echo "
		var Pigeon = new PigeonClass({
			subdomain:'".$this->pigeon_settings['subdomain']."',
			fingerprint:true,
			".(
				// If paywall is js and primary domain is not found in the subdomain, then use iFrame for SSO IDP
				$this->pigeon_settings["paywall"] == 2 && strstr($this->pigeon_settings['subdomain'],str_replace("www.","",$_SERVER["HTTP_HOST"])) === FALSE ? "idp:true,\n\t\t\t" : ""
			)
			."cid: ".( $this->pigeon_settings["paywall"] == 1 && array_key_exists( $pigeon_session . "_id", $_COOKIE ) ? $_COOKIE[$pigeon_session . "_id"] : "null" ) .",
			cha: ".( $this->pigeon_settings["paywall"] == 1 && array_key_exists( $pigeon_session . "_hash", $_COOKIE ) ? "'".$_COOKIE[$pigeon_session . "_hash"]."'" : "null" ) ."
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
				if( $this->pigeon_settings['content_value'] || $this->pigeon_settings['content_price'] || $this->pigeon_values['force_content_modal'] ){
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
		}

		// JS Plugin
		if( $this->pigeon_settings["paywall"] == 2 ){
			switch($this->pigeon_settings['paywall_interrupt']){
				case "1": $paywall_iterrupt = 1; break;
				case "2": $paywall_iterrupt = 0; break;
				case "3": $paywall_iterrupt = "'modal'"; break;
			}

			$is_page_free = $this->pigeon_content_access ? $this->pigeon_content_access : $this->pigeon_settings['content_access'];

			// Don't count the 404 pages.
			if( is_404() ){
				$is_page_free = 1;
			}

			echo "
				Pigeon.paywall({
					redirect:".$paywall_iterrupt.",
					free:".$is_page_free.",
					contentId:".($this->pigeon_content_id ? $this->pigeon_content_id : empty($this->pigeon_settings['content_id']) ? 0 : $this->pigeon_settings['content_id']).",
					contentTitle:'".urlencode($this->pigeon_content_title ? $this->pigeon_content_title : empty($this->pigeon_settings['content_title']) ? "" : $this->pigeon_settings['content_title'] )."',
					contentDate:'".urlencode($this->pigeon_content_date ? $this->pigeon_content_date : empty($this->pigeon_settings['content_date']) ? "" : $this->pigeon_settings['content_date'] )."',
					contentPrice:".($this->pigeon_content_price ? $this->pigeon_content_price : empty($this->pigeon_settings['content_price']) ? 0 : preg_replace("/([^0-9\.]+)/","",$this->pigeon_settings['content_price'])).",
					contentValue:".($this->pigeon_content_value ? $this->pigeon_content_value : empty($this->pigeon_settings['content_value']) ? 0 : $this->pigeon_settings['content_value']).",
					contentPrompt:".($this->pigeon_content_prompt ? $this->pigeon_content_prompt : empty($this->pigeon_settings['content_prompt']) ? 0 : $this->pigeon_settings['content_prompt']).",
					wpPostType: '".($this->pigeon_settings['wp_post_type'] ? $this->pigeon_settings['wp_post_type'] : "")."'
				});

				Pigeon.widget.status();";

			if( isset($this->reload_page) ){
				echo "
				Pigeon.paywallPromise.done(function(data){
					location.reload();
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

//		if( isset($this->pigeon_settings['subdomain']) )
//			wp_enqueue_script("pigeon", "//".$this->pigeon_settings['subdomain']."/c/assets/pigeon-1.5.2.min.js",array("jquery"), self::VERSION );

		add_action("wp_head", array($this, 'init_pigeon_js') );
	}

	/**
	 * Load Pigeon Shortcodes
	 *
	 * @since    1.4.8
	 */

	public function load_pigeon_shortcodes()
	{
		libxml_use_internal_errors(true);// TODO Turns off warnings for loadHTML
		function pigeon_protect_shortcode($atts=[], $content=null)
		{
			$pigeon_obj = WP_Pigeon::get_instance();
			// Handle shortcode differently base on the paywall mode

			// Server Side plugin
			if( $pigeon_obj->pigeon_settings["paywall"] == 1 ){
				if( ! $pigeon_obj->pigeon_values["allowed"] ){
					$content = "";
				}else{
//					ini_set( "display_errors", TRUE );
					$content = $pigeon_obj::parse_anchors($content,$pigeon_obj->pigeon_values["profile"]["customer_id"]);
					// run shortcode parser recursively
					$content = do_shortcode($content);
				}
			}

			// JS plugin
			if( $pigeon_obj->pigeon_settings["paywall"] == 2 ){

				// run shortcode parser recursively
				$content = do_shortcode($content);
				$content = '<div class="pigeon-remove">'.$content.'</div><div class="pigeon-context-promotion" style="display:none;"><p>This page is available to subscribers. <a href="#" class="pigeon-open">Click here to sign in or get access</a>.</p></div>';
			}

			return apply_filters('the_content', $content);
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
				// Handle display conditions from the js plugin
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

		function pigeon_content_expires_shortcode($atts=[], $content=null, $tag='')
		{
			$pigeon_obj = WP_Pigeon::get_instance();

			// normalize attribute keys, lowercase
			$atts = array_change_key_case((array)$atts, CASE_LOWER);

			$pigeon_atts = shortcode_atts([
				 'format' => 'F j, Y g:i A T',
			 ], $atts, $tag);

			// Handle shortcode differently base on the paywall mode

			// Server Side plugin
			if( $pigeon_obj->pigeon_settings["paywall"] == 1 ){
				if( array_key_exists("content_expires",$pigeon_obj->pigeon_values) ){
					$date = new DateTime($pigeon_obj->pigeon_values["content_expires"]);
					$content = $date->format($pigeon_atts["format"]);
				}
			}

			// JS plugin
			if( $pigeon_obj->pigeon_settings["paywall"] == 2 ){

				// run shortcode parser recursively
				$content = '<div class="pigeon-content-expires" data-format="'.$pigeon_atts["format"].'"></div>';
			}

			return $content;
		}

		add_shortcode('pigeon_content_expires', 'pigeon_content_expires_shortcode');
	}

	/**
	 * Make a request to the Pigeon Paywall
	 *
	 * @since    1.0.0
	 */
	public function make_pigeon_request() {

		// Avoid sending assets to the Pigeon server. Will reduce impression load and client cost.
		foreach ( array( ".css", ".js", ".woff", ".eot", ".ttf", ".svg", ".png", ".jpg", ".gif", ".cur", "css?" ) as $asset ) {
			if ( strpos( basename( $_SERVER["REQUEST_URI"] ), $asset ) !== FALSE ) {
				return;
			}
		}

		$admin_options = get_option( 'wp_pigeon_settings' );

		if ( is_admin() )
			return;

		if(array_key_exists("sucuriscan",$_GET)){
			echo "<!--sucuriscan-->";
		}

		// Load the API class
		require_once( plugin_dir_path( __FILE__ ) . 'includes/class-pigeon-api.php' );

		// Get our content access settings
		if ( is_singular() ) {
			global $post;

			$this->pigeon_settings['content_id'] = $post->ID;
			$this->pigeon_settings['content_title'] = $post->post_title;
			$this->pigeon_settings['content_date'] = $post->post_date_gmt;
			$this->pigeon_settings['content_access'] = get_post_meta( $post->ID, '_wp_pigeon_content_access', true );
			$this->pigeon_settings['wp_post_type'] = $post->post_type;

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

		// Paywall implementation
		$this->pigeon_settings['paywall'] = $admin_options["pigeon_paywall"];

		// Paywall interrupt method
		$this->pigeon_settings['paywall_interrupt'] = $admin_options["pigeon_paywall_interrupt"];

		// Subdomain

		// TODO STANDARDIZE THIS
//		if( $this->ip_info() == "US"){
//			$this->pigeon_settings['subdomain'] = $admin_options["pigeon_subdomain"] ? str_replace(array("https://","http://"),"",$admin_options["pigeon_subdomain"]): 'my.' . str_replace( 'www.', '', $_SERVER["HTTP_HOST"] );
//		}else{
//			$this->pigeon_settings['subdomain'] = "account.catholicherald.co.uk";
//		}

		$this->pigeon_settings['subdomain'] = $admin_options["pigeon_subdomain"] ? str_replace(array("https://","http://"),"",$admin_options["pigeon_subdomain"]): 'my.' . str_replace( 'www.', '', $_SERVER["HTTP_HOST"] );


		// User
		$this->pigeon_settings['user'] = $admin_options["pigeon_api_user"];

		// Secret key
		$this->pigeon_settings['secret'] = $admin_options["pigeon_api_secret_key"];

		// If the cookie is not set and we are in server mode, let js set the cookie so the fingerprint is checked.
		$pigeon_session = md5( $this->pigeon_settings['subdomain'] );

		if( $this->pigeon_settings["paywall"] == 1 && ! array_key_exists( $pigeon_session . "_id", $_COOKIE ) && !array_key_exists( $pigeon_session . "_hash", $_COOKIE ) ){
			$this->pigeon_settings["paywall"] = 2;
			// In order to utilize server-side security reload the page
			$this->reload_page = TRUE;
		}

		// Make the request
		if( $this->pigeon_settings['paywall'] == 1 ){
			$pigeon_api = new WP_Pigeon_Api;
			$this->pigeon_values = $pigeon_api->exec( $this->pigeon_settings );
//			print_r($admin_options);
//			print_r($this->pigeon_values);
			// If SSO and the user is not logged in on Pigeon, then make sure WP is logged out
			if( $admin_options["pigeon_wp_sso"] == 1 ){
				if( ! $this->pigeon_values["profile"]  ){
					if( is_user_logged_in() ){
						// Only logout accounts that are linked by pigeon_customer_id
						$pigeon_customer_id = get_user_meta(get_current_user_id(),'pigeon_customer_id', TRUE);
						if( $pigeon_customer_id ){
							wp_logout();
							header("Refresh:0");
//							echo "<!-- PIGEON NO PROFILE".$pigeon_customer_id." -->";
						}
					}
				}else{
					if( ! is_user_logged_in() ){
						$found_users = get_users(array("meta_key"=>"pigeon_customer_id","meta_value"=>$this->pigeon_values["profile"]["customer_id"],"number"=>'1'));

						if( count($found_users) == 1 ){
							$user_id = $found_users[0]->ID;
							$user_login = $found_users[0]->user_login;
						// Create new account and sync it
						}else{
							// Look for account by internal id or email to try to sync the accounts
							if( $wp_user = get_user_by("id",$this->pigeon_values["profile"]["internal_id"]) ){
								$user_id = $wp_user->ID;
							}elseif( $wp_user = get_user_by("email",$this->pigeon_values["profile"]["email"]) ){
								$user_id = $wp_user->ID;
								$this->pigeon_sdk->Customer->update($this->pigeon_values["profile"]["customer_id"],array("internal_id"=>$user_id));
							}else{
								$response = $this->pigeon_sdk->Customer->find($this->pigeon_values["profile"]["customer_id"]);
								$user_id = wp_insert_user( array(
									"user_login"=>$response->customer->email,
									"user_email"=>$response->customer->email,
									"user_pass"=> self::generate_random_string(),
									"display_name"=> $response->customer->display_name,
									"first_name"=> $response->customer->first_name,
									"last_name"=> $response->customer->last_name
								) );
								$this->pigeon_sdk->Customer->update($this->pigeon_values["profile"]["customer_id"],array("internal_id"=>$user_id));
							}
							if( $user_id ){
								add_user_meta($user_id,'pigeon_customer_id',$this->pigeon_values["profile"]["customer_id"]);
								$user_login = $response->customer->email;
							}
						}
						if( $user_id ){
							wp_set_current_user( $user_id, $user_login );
							wp_set_auth_cookie( $user_id );
							do_action( 'wp_login', $user_login );
						}

					// Log the user out because SSO says logins must match
					// The reload will run the code above
					}else{
						// Only logout accounts that are linked by pigeon_customer_id
						$pigeon_customer_id = get_user_meta(get_current_user_id(),'pigeon_customer_id', TRUE);
						if( $this->pigeon_values["profile"]["customer_id"] != $pigeon_customer_id ){
							wp_logout();
							header("Refresh:0");
//							echo "<!-- PIGEON HAS PROFILE".$pigeon_customer_id." -->";
						}
					}
				}
			}

		}else{
			$this->pigeon_values = $this->pigeon_settings;
		}
	}

	// HELPERS

	/**
	 * Parse anchors allows for securing of PDF file between pigeon_protect shortcodes
	 *
	 * @since    1.5.1
	 */

	static public function parse_anchors( $html_string, $customer_id )
	{
		$dom = new DOMDocument();
		$dom->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$html_string, LIBXML_HTML_NODEFDTD);
		$anchor_array = $dom->getElementsByTagName("a");

		// All tracker links developed above have an attribute of rel=trk, so only convert anchor URLs without this attribute and value
		foreach($anchor_array as $anchor){
			$parse_anchor = TRUE;

			foreach($anchor->attributes as $name=>$node){
				if( $name == "href" ){
					if(strpos($node->value, "#") === 0 )
						$parse_anchor = FALSE;

					if( strpos($node->value,".pdf") === FALSE )
						$parse_anchor = FALSE;

					$anchor_href = plugin_dir_url( __FILE__ )."download.php?auth=".base64_encode($node->value."?cuid=".$customer_id);
				}
			}

			if( $parse_anchor ){
				$anchor->setAttribute("href",$anchor_href);
			}
		}

		return $dom->saveHTML();

//		$body = $dom->getElementsByTagName('body')->item(0);
//		// perform innerhtml on $body by enumerating child nodes
//		// and saving them individually
//		$html = "";
//		if($body->childNodes) {
//			foreach ($body->childNodes as $childNode) {
//				$html .= $dom->saveHTML($childNode);
//			}
//		}
//
//		return $html;
	}

	// Used for unique strings with low security requirements.
	static public function generate_random_string( $length = 10 ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$characters_length = strlen($characters);
		$random_string = '';
		for ($i = 0; $i < $length; $i++) {
			$random_string .= $characters[rand(0, $characters_length - 1)];
		}
		return $random_string;
	}


	/**
	 * Single Sign-on in server mode only
	 *
	 * @since    1.5.0
	 */

	public function load_sso()
	{
		$admin_options = get_option( 'wp_pigeon_settings' );

		if( $admin_options && array_key_exists("pigeon_wp_sso",$admin_options) && $admin_options["pigeon_wp_sso"] == 1 ){
			if( ! $this->pigeon_sdk ){
				return TRUE;
			}

			// Logout WP session if the

			add_action('profile_update',array( $this, 'sso_user_sync'));
			add_action('user_register',array( $this, 'sso_user_sync'));
			add_action('wp_login', array($this, 'sso_user_login'), 10, 2);
			add_action('clear_auth_cookie', array($this, 'sso_user_logout'));
			add_action('wp_logout', array($this, 'sso_user_logout'));
		}
	}

	function sso_user_sync( $user_id )
	{
		$pigeon_customer_id = get_user_meta($user_id,'pigeon_customer_id', TRUE);
		// If the pigeon customer token is not set, look for customer by email, if not found then create and set new user
		$user_data = get_userdata($user_id);

		if( ! $pigeon_customer_id ){

			$response = $this->pigeon_sdk->Customer->search(array("search"=>$user_data->user_email,"limit"=>1));
			if( $response->results ){
				$pigeon_customer_id = $response->results[0]->id;
				add_user_meta($user_id,'pigeon_customer_id',$pigeon_customer_id);
			}else{
				$response = $this->pigeon_sdk->Customer->create(array(
					"email"=>$user_data->user_email,
					"wp_password"=>$user_data->user_pass,
					"display_name"=>$user_data->display_name,
					"send_notice"=>FALSE
				));

				$pigeon_customer_id = $response->customer->id;
				add_user_meta($user_id,'pigeon_customer_id',$pigeon_customer_id);
			}

		}else{
			$this->pigeon_sdk->Customer->update($pigeon_customer_id,array(
				"email"=>$user_data->user_email,
				"wp_password"=>$user_data->user_pass,
				"display_name"=>$user_data->display_name
			));
		}

	}

	public function sso_user_login($user_login, $wp_user)
	{
		$pigeon_customer_id = get_user_meta($wp_user->ID,'pigeon_customer_id', TRUE);
		if( $pigeon_customer_id ){
			$this->pigeon_sdk->Customer->sessionLogin($pigeon_customer_id);
		}
	}

	public function sso_user_logout()
	{
		$userinfo = wp_get_current_user();
		$pigeon_customer_id = get_user_meta($userinfo->ID,'pigeon_customer_id', TRUE);
		if( $pigeon_customer_id ){
			$this->pigeon_sdk->Customer->sessionLogout($pigeon_customer_id);
		}

	}

	public function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE)
	{
		if(array_key_exists("pi_geo_code", $_COOKIE)){
			return $_COOKIE["pi_geo_code"];
		}
		if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
			$ip = $_SERVER["REMOTE_ADDR"];
			if ($deep_detect) {
				if (array_key_exists("HTTP_X_FORWARDED_FOR",$_SERVER) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				if (array_key_exists("HTTP_CLIENT_IP",$_SERVER) && filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
					$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
		}

		$result = $this->pigeon_sdk->get("/customer/ip_info",["ip"=>$ip,"purpose"=>"country_code"]);
		$user_geo_country_code = $result->data;
		try{
			setcookie( "pi_geo_code", $user_geo_country_code, time() + 3600*24, "/");
		}catch( Exception $e ){
			// fail silently
		}
		return $user_geo_country_code;
	}


	public function set_status_by_post_type( $post_type, $status="" )
	{
		$wpdb = $GLOBALS["wpdb"];

		$query = "SELECT
					*
					FROM
					  ".$wpdb->prefix."posts
				   WHERE
					  post_type='".$post_type."'";

		$posts = $wpdb->get_results( $query, ARRAY_A );

		$pigeon_content_access = intval( $status );

		foreach($posts as $post ){
			update_post_meta( $post["ID"], '_wp_pigeon_content_access', $pigeon_content_access );
		}
	}
}

<?php
/**
 * Pigeon for WordPress
 *
 * @package   Pigeon for WordPress
 * @author    Matt Geri / Jonathan Wold
 * @license   GPL-2.0+
 * @link      http://pigeonpaywall.com/
 * @copyright 2014 Sabramedia
 */

/**
 * The core admin class
 *
 * @package WP_Pigeon
 * @author  Sabramedia
 */
class WP_Pigeon_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = WP_Pigeon::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		
		// Register the settings 
		add_action( 'admin_menu', array( $this, 'plugin_settings_init' ) );

		// Load JS
		add_action( 'admin_enqueue_scripts', array( $this, 'load_pigeon_admin_js' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		// Add our meta box for posts, pages and custom post types
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

        // Load functions
        add_action( 'admin_init', array( $this, 'load_pigeon_functions' ) );
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
	 * Adds the Pigeon meta box
	 *
	 * @since     1.0.0
	 */
	public function add_meta_box() {
		$options = get_option( 'wp_pigeon_settings' );
		$custom_post_types = array_key_exists("pigeon_content_post_types", $options) && $options['pigeon_content_post_types'] ? $options['pigeon_content_post_types'] : array();

		foreach ( array_merge( array( 'post', 'page' ), $custom_post_types) as $post_type )
			add_meta_box( 'wp_pigeon', 'Pigeon Settings', array( $this, 'display_meta_box' ), $post_type, 'side', 'high' );
	
	}

	/**
	 * Displays the content for the meta box
	 *
	 * @since     1.0.0
	 */
	public function display_meta_box( $post ) {
		
		wp_nonce_field( 'wp_pigeon', 'wp_pigeon_nonce' );

		$access_value = get_post_meta( $post->ID, '_wp_pigeon_content_access', true );
		$content_price = get_post_meta( $post->ID, '_wp_pigeon_content_price', true );
		$content_value = get_post_meta( $post->ID, '_wp_pigeon_content_value', true );
		$content_prompt = get_post_meta( $post->ID, '_wp_pigeon_content_prompt', true );

		$options = get_option( 'wp_pigeon_settings' );

		include_once( 'views/meta-box.php' );

	}

	/**
	 * Saves the metabox form
	 *
	 * @since     1.0.0
	 */
	public function save_meta_box( $post_id ) {

		if ( ! isset( $_POST['wp_pigeon_nonce'] ) )
			return $post_id;

		$nonce = $_POST['wp_pigeon_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'wp_pigeon' ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		$pigeon_content_access = intval( $_POST['pigeon_content_access'] );

		if( array_key_exists('pigeon_content_price', $_POST)){
			update_post_meta( $post_id, '_wp_pigeon_content_price', $_POST['pigeon_content_price'] );
		}

		if( array_key_exists('pigeon_content_value', $_POST)){
			$pigeon_content_value = intval( $_POST['pigeon_content_value'] );
			update_post_meta( $post_id, '_wp_pigeon_content_value', $pigeon_content_value );
			if(array_key_exists("pigeon_content_prompt",$_POST)){
				update_post_meta( $post_id, '_wp_pigeon_content_prompt', 1 );
			}else{
				update_post_meta( $post_id, '_wp_pigeon_content_prompt', 0 );
			}
		}

		update_post_meta( $post_id, '_wp_pigeon_content_access', $pigeon_content_access );

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Pigeon', $this->plugin_slug ),
			__( 'Pigeon', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Load Pigeon JS
	 *
	 * @since    1.4.0
	 */
	public function load_pigeon_admin_js( $hook ) {

		if( $hook == "settings_page_wp-pigeon" )
			wp_enqueue_script( 'pigeon_admin', plugin_dir_url( __FILE__ ) . 'js/settings.js' );
	}
	
	/* Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.1.0
	 */
	public function plugin_settings_init() {

		register_setting( 'plugin_options', 'wp_pigeon_settings' );
		
		// Register our sections
		$this->plugin_screen_hook_suffix = add_settings_section(
			'settings_section_basic', 
			__( 'Basic Configuration', $this->plugin_slug ),  
			array( $this, 'settings_section_basic_callback' ),
			'plugin_options'
		);
		
		$this->plugin_screen_hook_suffix = add_settings_section(
			'settings_section_api', 
			__( 'API Connection', $this->plugin_slug ),  
			array( $this, 'settings_section_api_callback' ),
			'plugin_options'
		);

		$this->plugin_screen_hook_suffix = add_settings_section(
			'settings_section_content',
			__( 'Content', $this->plugin_slug ),
			array( $this, 'settings_section_content_callback' ),
			'plugin_options'
		);
		
		// Register our fields 
		
		$this->plugin_screen_hook_suffix = add_settings_field(
			'pigeon_subdomain', 
			__( 'Pigeon Subdomain', $this->plugin_slug ),  
			array( $this, 'setting_pigeon_subdomain_render' ),
			'plugin_options',
			'settings_section_basic',
			array("class"=>"test")
		);
		
//		$this->plugin_screen_hook_suffix = add_settings_field(
//			'pigeon_redirect',
//			__( 'Redirect', $this->plugin_slug ),
//			array( $this, 'setting_pigeon_redirect_render' ),
//			'plugin_options',
//			'settings_section_basic'
//		);

		$this->plugin_screen_hook_suffix = add_settings_field(
			'pigeon_paywall',
			__( 'Paywall', $this->plugin_slug ),
			array( $this, 'setting_pigeon_paywall_render' ),
			'plugin_options',
			'settings_section_basic'
		);

		$this->plugin_screen_hook_suffix = add_settings_field(
			'pigeon_paywall_interrupt',
			__( 'Paywall Interrupt', $this->plugin_slug ),
			array( $this, 'setting_pigeon_paywall_interrupt_render' ),
			'plugin_options',
			'settings_section_basic'
		);
		
		
		$this->plugin_screen_hook_suffix = add_settings_field(
			'pigeon_api_user', 
			__( 'User', $this->plugin_slug ),  
			array( $this, 'setting_pigeon_api_user_render' ),
			'plugin_options',
			'settings_section_api'
		);
		
		$this->plugin_screen_hook_suffix = add_settings_field(
			'pigeon_api_secret_key', 
			__( 'Private Key', $this->plugin_slug ),
			array( $this, 'setting_pigeon_api_secret_key_render' ),
			'plugin_options',
			'settings_section_api'
		);

		$this->plugin_screen_hook_suffix = add_settings_field(
			'pigeon_content_value_pricing',
			__( 'Pricing Value', $this->plugin_slug ),
			array( $this, 'setting_pigeon_content_value_pricing_render' ),
			'plugin_options',
			'settings_section_content'
		);

		$this->plugin_screen_hook_suffix = add_settings_field(
			'pigeon_content_value_meter',
			__( 'Value Meter', $this->plugin_slug ),
			array( $this, 'setting_pigeon_content_value_meter_render' ),
			'plugin_options',
			'settings_section_content'
		);

		$this->plugin_screen_hook_suffix = add_settings_field(
			'pigeon_content_value',
			__( 'Credit Value', $this->plugin_slug ),
			array( $this, 'setting_pigeon_content_value_render' ),
			'plugin_options',
			'settings_section_content'
		);

		$this->plugin_screen_hook_suffix = add_settings_field(
			'pigeon_content_post_types',
			__( 'Post Types', $this->plugin_slug ),
			array( $this, 'setting_pigeon_content_post_type_render' ),
			'plugin_options',
			'settings_section_content'
		);

        $this->plugin_screen_hook_suffix = add_settings_field(
            'pigeon_content_pref_category',
            __( 'Category Preferences', $this->plugin_slug ),
            array( $this, 'setting_pigeon_content_category_render' ),
            'plugin_options',
            'settings_section_api'
        );
//      TODO deprecate in favor of Pigeon being the primary identity provider.
//		$this->plugin_screen_hook_suffix = add_settings_field(
//			'pigeon_wp_sso',
//			__( 'Single Sign-on (WP to Pigeon)', $this->plugin_slug ),
//			array( $this, 'setting_pigeon_wp_sso' ),
//			'plugin_options',
//			'settings_section_api'
//		);
	}

	/* Basic section settings callback
	 *
	 * @since    1.1.0
	 */
	public function settings_section_basic_callback() {

		// echo __( 'Basic section description', $this->plugin_slug );

	}

	/* API Section settings callback
	 *
	 * @since    1.1.0
	 */
	public function settings_section_api_callback() {

		// echo __( 'API section Description', $this->plugin_slug );

	}
	/* Content Section settings callback
	 *
	 * @since    1.4.0
	 */
	public function settings_section_content_callback() {

		 echo __( 'Only used when content value needs to be set in WordPress and passed to Pigeon.', $this->plugin_slug );

	}


	/* Pigeon subdomain callback
	 *
	 * @since    1.1.0
	 */
	public function setting_pigeon_subdomain_render() {
		$options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];
	?>

		<input type='text' name='wp_pigeon_settings[pigeon_subdomain]' value='<?php echo $options['pigeon_subdomain']; ?>'>
		<p class="description">Defines the subdomain used for Pigeon.</p>

	<?php

	}

	/* Pigeon redirect callback
	 *
	 * @since    1.1.0
	 */
	public function setting_pigeon_redirect_render() {

		$options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];

		$html  = '<input type="radio" id="redirect_enabled" name="wp_pigeon_settings[pigeon_redirect]" value="1"' . checked( 1, $options['pigeon_redirect'], false ) . '/>';
		$html .= '<label for="redirect_enabled">Enabled</label> ';

		$html .= '<input type="radio" id="redirect_disabled" name="wp_pigeon_settings[pigeon_redirect]" value="2"' . checked( 2, $options['pigeon_redirect'], false ) . '/>';
		$html .= '<label for="redirect_disabled">Disabled</label>';

		$html .= '<p class="description">Determines whether the plugin does the automatic reroute or stays on the page.</p>';

		echo $html;

	}

	/* Pigeon Paywall plugin technology server | browser
	 *
	 * @since    1.3.0
	 */
	public function setting_pigeon_paywall_render() {

		$options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];

		$html  = '<input type="radio" id="paywall_server" name="wp_pigeon_settings[pigeon_paywall]" value="1"' . checked( 1, $options['pigeon_paywall'], false ) . '/>';
		$html .= '<label for="paywall_server">Server</label> ';

		$html .= '<input type="radio" id="paywall_js" name="wp_pigeon_settings[pigeon_paywall]" value="2"' . checked( 2, $options['pigeon_paywall'], false ) . '/>';
		$html .= '<label for="paywall_js">JavaScript</label>';

		$html .= '<p class="description">Use JavaScript if you have a metered paywall or want to use the modal popup.</p>';

		echo $html;

	}

	/* Pigeon Paywall interrupt type
	 *
	 * @since    1.3.0
	 */
	public function setting_pigeon_paywall_interrupt_render() {

		$options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];

		$html  = '<input type="radio" id="paywall_interrupt_redirect" name="wp_pigeon_settings[pigeon_paywall_interrupt]" value="1"' . checked( 1, $options['pigeon_paywall_interrupt'], false ) . '/>';
		$html .= '<label for="paywall_server">Redirect</label> ';

		$html .= '<input type="radio" id="paywall_interrupt_modal" name="wp_pigeon_settings[pigeon_paywall_interrupt]" value="3"' . checked( 3, $options['pigeon_paywall_interrupt'], false ) . '/>';
		$html .= '<label for="paywall_js">Modal Popup</label>';

		$html .= '<input type="radio" id="paywall_interrupt_custom" name="wp_pigeon_settings[pigeon_paywall_interrupt]" value="2"' . checked( 2, $options['pigeon_paywall_interrupt'], false ) . '/>';
		$html .= '<label for="paywall_js">Custom</label>';

		$html .= '<p class="description">Redirect respects paywall rules. Modal uses the default Pigeon modal. Custom allows you to take your own actions. Refer to documentation on how to do this.</p>';

		echo $html;

	}


	/* API user callback
	 *
	 * @since    1.1.0
	 */
	public function setting_pigeon_api_user_render() {
		$options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];
	?>

		<input type='text' name='wp_pigeon_settings[pigeon_api_user]' value='<?php echo $options['pigeon_api_user']; ?>'>

	<?php

	}


	/* API secret key callback
	 *
	 * @since    1.1.0
	 */
	public function setting_pigeon_api_secret_key_render() {
		$options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];
	?>

		<input type='text' name='wp_pigeon_settings[pigeon_api_secret_key]' value='<?php echo $options['pigeon_api_secret_key']; ?>'>

	<?php

        if( $options['pigeon_api_user'] && $options['pigeon_api_secret_key'] ) {
			try {
				require_once(plugin_dir_path(__FILE__) . "../sdk/Pigeon.php");
				Pigeon_Configuration::clientId($options['pigeon_api_user']);
				Pigeon_Configuration::apiKey($options['pigeon_api_secret_key']);
				Pigeon_Configuration::pigeonDomain($options['pigeon_subdomain']);

				// Send the category array
				$pigeon_sdk = new Pigeon();
				// Make a call to see if it works.
				$pigeon_sdk->get("",[]);
			} catch (Exception $e) {
				echo "<p style=\"color:#ca4a1f\">There is a connectivity issue. Make sure the Pigeon API credentials are correct. This plugin uses cURL. Please make sure this is enabled in order for the direct API to work.</p>";
			}
		}

	}



	/* Content value pricing on or off
	 *
	 * @since    1.4.7
	 */
	public function setting_pigeon_content_value_pricing_render() {

		$options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];
		$options['pigeon_content_value_pricing'] = array_key_exists("pigeon_content_value_pricing", $options) ? $options['pigeon_content_value_pricing'] : 2;
		$html  = '<input type="radio" id="value_pricing_enabled" class="pigeon-value-pricing" name="wp_pigeon_settings[pigeon_content_value_pricing]" value="1"' . checked( 1, $options['pigeon_content_value_pricing'], false ) . '/>';
		$html .= '<label for="value_pricing_enabled">Enabled</label> ';

		$html .= '<input type="radio" id="value_pricing_disabled" class="pigeon-value-pricing" name="wp_pigeon_settings[pigeon_content_value_pricing]" value="2"' . checked( 2, $options['pigeon_content_value_pricing'], false ) . '/>';
		$html .= '<label for="value_pricing_disabled">Disabled</label>';

		echo $html;

	}

	/* Content value meter on or off
	 *
	 * @since    1.4.0
	 */
	public function setting_pigeon_content_value_meter_render() {

		$options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];

		$options['pigeon_content_value_meter'] = array_key_exists("pigeon_content_value_meter", $options) ? $options['pigeon_content_value_meter'] : 2;
		$html  = '<input type="radio" id="value_meter_enabled" class="pigeon-value-meter" name="wp_pigeon_settings[pigeon_content_value_meter]" value="1"' . checked( 1, $options['pigeon_content_value_meter'], false ) . '/>';
		$html .= '<label for="value_meter_enabled">Enabled</label> ';

		$html .= '<input type="radio" id="value_meter_disabled" class="pigeon-value-meter" name="wp_pigeon_settings[pigeon_content_value_meter]" value="2"' . checked( 2, $options['pigeon_content_value_meter'], false ) . '/>';
		$html .= '<label for="value_meter_disabled">Disabled</label>';

		echo $html;

	}

	/* Content value list
	 *
	 * @since    1.4.0
	 */
	public function setting_pigeon_content_value_render() {
		$options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];

		// preset empty array if not set
		if( !isset($options['pigeon_content_value']) )
			$options['pigeon_content_value'] = array("");

		foreach( $options['pigeon_content_value'] as $option ){
	?>
		<div class="pigeon-content-value-option">
			<input type='text' name='wp_pigeon_settings[pigeon_content_value][]' value='<?php echo $option; ?>'>
			<button class="remove">Remove</button>
		</div>
	<?php
		}
	?>
		<div class="pigeon-add-content-value">
			<button>Add New Value</button>
		</div>
	<?php
	}

	/* Content value list
	 *
	 * @since    1.4.0
	 */
	public function setting_pigeon_content_post_type_render() {
		$options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];

		// preset empty array if not set
		if( !isset($options['pigeon_content_post_types']) )
			$options['pigeon_content_value'] = array("");

		$post_types = get_post_types( array("public"=>true,"_builtin"=>false), "names", "and" );

		if( $post_types ){

		$post_type_options = $options['pigeon_content_post_types'] ? $options['pigeon_content_post_types'] : array();

		foreach( $post_types as $option ){
			$checked = false;
			if( in_array( $option, $post_type_options))
				$checked = true;
	?>
		<div class="pigeon-content-post-type-option">
			<input type='checkbox' name='wp_pigeon_settings[pigeon_content_post_types][]' value='<?php echo $option; ?>'<?php echo $checked ? " checked" : ""; ?> /> <?php echo $option; ?>
		</div>
	<?php
		}
		}else{
	?>
		<div class="pigeon-add-post-type">
			There are no custom post types available.
		</div>
	<?php
	}}

    /* Content category preferences on or off
     *
     * @since    1.5.9
     */
    public function setting_pigeon_content_category_render()
    {
        $options = get_option( 'wp_pigeon_settings' );
        $options = $options ? $options : [];
        $required_note = "";

        // TODO This may be a bit non-standard, but if the page loads with the plugin enabled, then run an api call to enable the plugin
        // Only run the following if the api keys are set.
        if( $options['pigeon_api_user'] && $options['pigeon_api_secret_key'] ) {
			if (isset($_GET["settings-updated"]) && $_GET["settings-updated"] == "true") {
				if (array_key_exists("pigeon_content_pref_category", $options) && $options['pigeon_content_pref_category'] == 1) {
					pigeon_category_enable();
				} else {
					pigeon_category_disable();
				}
			}
		}else{
            $required_note = "<strong>Requires API User and Private Key from Settings > API in your Pigeon dashboard.</strong> ";
        }

        $options['pigeon_content_pref_category'] = array_key_exists("pigeon_content_pref_category", $options) ? $options['pigeon_content_pref_category'] : 2;
        $html  = '<input type="radio" id="category_pref_enabled" class="pigeon-content-pref-category" name="wp_pigeon_settings[pigeon_content_pref_category]" value="1"' . checked( 1, $options['pigeon_content_pref_category'], false ) . '/>';
        $html .= '<label for="category_pref_enabled">Enabled</label> ';

        $html .= '<input type="radio" id="category_pref_disabled" class="pigeon-value-meter" name="wp_pigeon_settings[pigeon_content_pref_category]" value="2"' . checked( 2, $options['pigeon_content_pref_category'], false ) . '/>';
        $html .= '<label for="category_pref_disabled">Disabled</label>';

        $html .= '<p class="description">'.$required_note.'Enable to send Post Categories to Pigeon. Registered users can choose which content categories they prefer.</p>';

        echo $html;

    }

	/* Content value meter on or off
	 *
	 * @since    1.5.0
	 */
	public function setting_pigeon_wp_sso() {

		$options = get_option( 'wp_pigeon_settings' );


        $options['pigeon_wp_sso'] = is_array($options) && array_key_exists("pigeon_wp_sso", $options) ? $options['pigeon_wp_sso'] : 2;
		$html  = '<input type="radio" id="value_sso_enabled" class="pigeon-wp-sso" name="wp_pigeon_settings[pigeon_wp_sso]" value="1"' . checked( 1, $options['pigeon_wp_sso'], false ) . '/>';
		$html .= '<label for="value_sso_enabled">Enabled</label> ';

		$html .= '<input type="radio" id="value_sso_disabled" class="pigeon-wp-sso" name="wp_pigeon_settings[pigeon_wp_sso]" value="2"' . checked( 2, $options['pigeon_wp_sso'], false ) . '/>';
		$html .= '<label for="value_sso_disabled">Disabled</label>';

		$html .= '<p class="description">Enable when you want WordPress to manage your user profile data instead of Pigeon.</p>';

		echo $html;

	}
	

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {

		include_once( 'views/admin.php' );

	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

    /**
     * Load Pigeon Functions
     *
     * @since    1.5.9
     */
    public function load_pigeon_functions() {
        // Include our custom functions
        require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );

    }

    /**
     * Load the Pigeon Direct API SDK
     *
     * @since    1.5.9
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
            add_action( 'init', array( $this, 'load_sdk' ) );
        }
    }
}
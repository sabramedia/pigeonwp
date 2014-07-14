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

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		// Add our meta box for posts, pages and custom post types
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

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
		
		foreach ( array( 'post', 'page' ) as $post_type )
			add_meta_box( 'wp_pigeon', 'Pigeon Access Status', array( $this, 'display_meta_box' ), $post_type, 'side', 'high' );
	
	}

	/**
	 * Displays the content for the meta box
	 *
	 * @since     1.0.0
	 */
	public function display_meta_box( $post ) {
		
		wp_nonce_field( 'wp_pigeon', 'wp_pigeon_nonce' );

		$value = get_post_meta( $post->ID, '_wp_pigeon_content_access', true );

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

		update_post_meta( $post_id, '_wp_pigeon_content_access', $pigeon_content_access );

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Pigeon Settings', $this->plugin_slug ),
			__( 'Pigeon Settings', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

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

}

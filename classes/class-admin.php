<?php
/**
 * Pigeon for WordPress
 *
 * @package   Pigeon for WordPress
 * @author    Pigeon <support@pigeon.io>
 * @license   GPL-2.0+
 * @link      http://pigeon.io
 * @copyright 2014 Sabramedia
 */

namespace PigeonWP;

/**
 * Class Admin.
 *
 * The core admin class.
 *
 * @since 1.0
 */
class Admin {

	/**
	 * The WordPress menu slug for the admin page.
	 *
	 * @since 1.6
	 */
	const MENU_SLUG = 'pigeon';

	/**
	 * ID for the meta box.
	 *
	 * @since 1.6
	 */
	const META_BOX_ID = 'pigeonwp';

	/**
	 * Nonce Action.
	 *
	 * @since 1.6
	 */
	const NONCE_ACTION = 'pigeonwp';

	/**
	 * The nonce name.
	 *
	 * @since 1.6
	 */
	const NONCE_NAME = 'pigeon_nonce';

	/**
	 * The JS handle for the settings.
	 *
	 * @since 1.6
	 */
	const JS_HANDLE = 'pigeon_admin';

	/**
	 * Hooks.
	 *
	 * @since 1.6
	 * @return void
	 */
	public function hooks() {
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add admin bar item.
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_item' ), 100 );

		// Load JS.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Admin CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'add_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ) );

		// Add an action link pointing to the options page.
		add_filter( 'plugin_action_links_' . PIGEONWP_BASENAME, array( $this, 'add_action_links' ) );

		// Add our meta box for posts, pages and custom post types.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

		// Redirect to settings page on plugin activation.
		add_action( 'activated_plugin', array( $this, 'redirect_on_activation' ) );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_plugin_admin_menu() {
		add_options_page(
			__( 'Pigeon', 'pigeon' ),
			__( 'Pigeon', 'pigeon' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Add menu item to the admin bar for Pigeon/
	 *
	 * @since 1.6.4
	 *
	 * @param object $wp_admin_bar The admin bar object.
	 *
	 * @return void
	 */
	public function add_admin_bar_item( $wp_admin_bar ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$title    = __( 'Pigeon', 'pigeon' );
		$meta     = array();
		$settings = get_plugin_settings();
		$demo     = ! empty( $settings['pigeon_demo'] ) ? $settings['pigeon_demo'] : 0;

		if ( $demo ) {
			$title .= ': ' . __( 'Demo Mode', 'pigeon' );

			$meta['class'] = 'pigeon_demo_mode';
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'pigeon',
				'parent' => null,
				'href'   => admin_url( 'options-general.php?page=pigeon' ),
				'title'  => $title,
				'meta'   => $meta,
			)
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function display_plugin_admin_page() {
		include_once PIGEONWP_DIR . 'templates/admin/admin.php';
	}

	/**
	 * Load Pigeon admin scripts.
	 *
	 * @since 1.4.0
	 * @param string $hook The page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'settings_page_' . self::MENU_SLUG === $hook ) {
			wp_enqueue_script( self::JS_HANDLE, PIGEONWP_URL . 'src/admin/settings.js', array(), PIGEONWP_VERSION, array( 'in_footer' => false ) );
		}
	}

	/**
	 * Add admin styles.
	 *
	 * @since 1.6.4
	 *
	 * @return void
	 */
	public function add_styles() {
		if ( current_user_can( 'activate_plugins' ) ) {
			$css = '
				#wp-admin-bar-pigeon.pigeon_demo_mode a, 
				#wp-admin-bar-pigeon.pigeon_demo_mode a:hover {
					background-color: #FDCF1A;
					color: #1d2327;
				}
			';
			wp_add_inline_style( 'admin-bar', $css );
		}
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since 1.0.0
	 * @param array $links List of links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . self::MENU_SLUG ) . '">' . __( 'Settings', 'pigeon' ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Adds the Pigeon meta box.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_meta_box() {
		$settings = get_plugin_settings();

		$custom_post_types = ! empty( $settings['pigeon_content_post_types'] ) ? $settings['pigeon_content_post_types'] : array();

		foreach ( array_merge( array( 'post', 'page' ), $custom_post_types ) as $post_type ) {
			add_meta_box( self::META_BOX_ID, __( 'Pigeon Settings', 'pigeon' ), array( $this, 'display_meta_box' ), $post_type, 'side', 'high' );
		}
	}

	/**
	 * Displays the content for the meta box.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post The post object.
	 * @return void
	 */
	public function display_meta_box( $post ) { // @phpcs:ignore
		include_once PIGEONWP_DIR . 'templates/admin/meta-box.php';
	}

	/**
	 * Saves the metabox form.
	 *
	 * @since 1.0.0
	 * @param int $post_id The ID of the post.
	 * @return void|int
	 */
	public function save_meta_box( $post_id ) {
		if ( empty( $_POST[ self::NONCE_NAME ] ) ) {
			return $post_id;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( isset( $_POST['pigeon_content_access'] ) ) {
			$pigeon_content_access = intval( wp_unslash( $_POST['pigeon_content_access'] ) );
			update_post_meta( $post_id, '_wp_pigeon_content_access', $pigeon_content_access );
		}

		if ( isset( $_POST['pigeon_content_price'] ) ) {
			$pigeon_content_price = sanitize_text_field( wp_unslash( $_POST['pigeon_content_price'] ) );
			update_post_meta( $post_id, '_wp_pigeon_content_price', $pigeon_content_price );
		}

		if ( isset( $_POST['pigeon_content_value'] ) ) {
			$pigeon_content_value = intval( wp_unslash( $_POST['pigeon_content_value'] ) );
			update_post_meta( $post_id, '_wp_pigeon_content_value', $pigeon_content_value );

			if ( ! empty( $_POST['pigeon_content_prompt'] ) ) {
				update_post_meta( $post_id, '_wp_pigeon_content_prompt', 1 );
			} else {
				update_post_meta( $post_id, '_wp_pigeon_content_prompt', 0 );
			}
		}
	}

	/**
	 * Redirect on activation.
	 *
	 * @since 1.6.1
	 * @param string $plugin The plugin being activated.
	 * @return void
	 */
	public function redirect_on_activation( $plugin ) {
		if ( PIGEONWP_BASENAME !== $plugin ) {
			return;
		}

		if ( ! is_admin() ) {
			return;
		}

		if ( is_network_admin() ) {
			return;
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			return;
		}

		$action = '';
		if ( ! empty( $_REQUEST['action'] ) ) { // @phpcs:ignore
			$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // @phpcs:ignore
		}

		if ( ! empty( $_REQUEST['activate-multi'] ) || 'activate-selected' === $action ) { // @phpcs:ignore
			return;
		}

		wp_safe_redirect( admin_url( 'options-general.php?page=' . self::MENU_SLUG ) );
		exit;
	}
}

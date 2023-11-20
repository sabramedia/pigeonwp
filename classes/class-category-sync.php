<?php
/**
 * Category syncing.
 *
 * @since 1.6
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Class Category_Sync
 *
 * Syncs categories between Pigeon and WordPress.
 *
 * @since 1.6
 */
class Category_Sync {

	/**
	 * Pigeon SDK.
	 *
	 * @since 1.6
	 *
	 * @var \Pigeon
	 */
	public $sdk;

	/**
	 * Hooks.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_sdk' ) );

		$settings = get_plugin_settings();

		// Only add the hooks if the category preferences is enabled.
		if ( ! empty( $settings['pigeon_content_pref_category'] ) ) {
			add_action( 'created_term', array( $this, 'category_save' ) );
			add_action( 'edited_term', array( $this, 'category_save' ) );
			add_action( 'delete_category', array( $this, 'category_save' ) );
		}
	}

	/**
	 * Load the SDK for use in class.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function load_sdk() {
		// Load the API class.
		require_once PIGEONWP_DIR . 'sdk/Pigeon.php';

		$settings = get_plugin_settings();

		\Pigeon_Configuration::clientId( $settings['pigeon_api_user'] );
		\Pigeon_Configuration::apiKey( $settings['pigeon_api_secret_key'] );
		\Pigeon_Configuration::pigeonDomain( $settings['pigeon_subdomain'] );

		// Send the category array.
		$this->sdk = new \Pigeon();
	}

	/**
	 * Sync post category data with Pigeon servers so registered users can pick category preferences.
	 *
	 * @since     1.5.9
	 *
	 * @return void
	 */
	public function category_save() {
		// Get categories.
		$args = array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => '0',
		);

		$this->sdk->post( '/plugin/wp_preferences/sync_category', array( 'categories' => get_categories( $args ) ) );
	}

	/**
	 * Enable the category on Pigeon.
	 *
	 * @since     1.5.9
	 *
	 * @return boolean
	 */
	public function pigeon_category_enable() {
		$settings = get_plugin_settings();

		if ( ! empty( $settings['pigeon_api_user'] ) && ! empty( $settings['pigeon_api_secret_key'] ) ) {
			try {
				$this->sdk->post( '/plugin/wp_preferences/enable_category_plugin', array() );
				return true;
			} catch ( \Exception $e ) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Disable the category on Pigeon.
	 *
	 * @since     1.5.9
	 *
	 * @return boolean
	 */
	public function pigeon_category_disable() {
		$settings = get_plugin_settings();

		if ( ! empty( $settings['pigeon_api_user'] ) && ! empty( $settings['pigeon_api_secret_key'] ) ) {
			try {
				$this->sdk->post( '/plugin/wp_preferences/disable_category_plugin', array() );
				return true;
			} catch ( \Exception $e ) {
				return false;
			}
		}

		return false;
	}
}

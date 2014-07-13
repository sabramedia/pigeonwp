<?php
/**
 * WP Pidgeon
 *
 * The Pidgeon Paywall plugin for WordPress
 *
 * @package   WP_Pidgeon
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://pigeonpaywall.com/
 * @copyright 2014 Sabramedia
 *
 * @wordpress-plugin
 * Plugin Name:       WP Pidgeon
 * Plugin URI:        http://pigeonpaywall.com/
 * Description:       The Pidgeon Paywall plugin for WordPress
 * Version:           1.0.0
 * Author:            @TODO
 * Author URI:        @TODO
 * Text Domain:       wp-pidgeon-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * Load the main WP Pidgeon class
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-wp-pidgeon.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'WP_Pidgeon', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Pidgeon', 'deactivate' ) );

/*
 * Actually load the plugin by creating an instance
 */
add_action( 'plugins_loaded', array( 'WP_Pidgeon', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * Set up the admin area
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wp-pidgeon-admin.php' );
	add_action( 'plugins_loaded', array( 'WP_Pidgeon_Admin', 'get_instance' ) );

}

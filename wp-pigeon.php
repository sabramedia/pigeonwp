<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   WP_Pidgeon
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 *
 * @wordpress-plugin
 * Plugin Name:       WP Pidgeon
 * Plugin URI:        @TODO
 * Description:       @TODO
 * Version:           1.0.0
 * Author:            @TODO
 * Author URI:        @TODO
 * Text Domain:       plugin-name-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 * WordPress-Plugin-Boilerplate: v2.6.1
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

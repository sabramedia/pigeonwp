<?php
/**
 * Pigeon for WordPress
 *
 * The Pigeon Paywall plugin for WordPress
 *
 * @package   Pigeon for WordPress
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://pigeonpaywall.com/
 * @copyright 2014-2015 Sabramedia
 *
 * @wordpress-plugin
 * Plugin Name:       Pigeon for WordPress
 * Plugin URI:        http://pigeonpaywall.com/
 * Description:       The Pigeon Paywall plugin for WordPress
 * Version:           1.4.6
 * Author:            Sabramedia
 * Text Domain:       wp-pigeon-locale
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
 * Load the main WP Pigeon class
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-wp-pigeon.php' );

/*
 * Actually load the plugin by creating an instance
 */
add_action( 'plugins_loaded', array( 'WP_Pigeon', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * Set up the admin area
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wp-pigeon-admin.php' );
	add_action( 'plugins_loaded', array( 'WP_Pigeon_Admin', 'get_instance' ) );

}

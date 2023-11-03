<?php
/**
 * Pigeon for WordPress
 *
 * The Pigeon Paywall plugin for WordPress
 *
 * @package   Pigeon for WordPress
 * @author    Pigeon <support@pigeon.io>
 * @license   GPL-2.0+
 * @link      http://pigeon.io
 * @copyright 2014-2019 Sabramedia
 *
 * @wordpress-plugin
 * Plugin Name:       Pigeon for WordPress
 * Plugin URI:        http://pigeon.io
 * Description:       The Pigeon Paywall plugin for WordPress
 * Version:           1.6
 * Author:            Sabramedia
 * Text Domain:       pigeonwp
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
 * Load the core plugin files.
 */
require_once 'config/config.php';
require_once 'public/class-wp-pigeon.php';

/*
 * Actually load the plugin by creating an instance.
 */
add_action( 'plugins_loaded', array( 'WP_Pigeon', 'get_instance' ) );

/*
 * Set up the admin area.
 */
if ( is_admin() ) {
	require_once 'admin/class-wp-pigeon-admin.php';

	add_action( 'plugins_loaded', array( 'WP_Pigeon_Admin', 'get_instance' ) );
}

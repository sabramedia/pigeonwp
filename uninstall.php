<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   PigeonWP
 * @author    Pigeon <support@pigeon.io>
 * @license   GPL-2.0+
 * @link      http://pigeon.io
 * @copyright 2014 Sabramedia
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

<?php
/**
 * Configs used throughout the plugin.
 *
 * @since 1.6
 *
 * @package PigeonWP
 */

namespace PigeonWP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'PIGEONWP_VERSION', '1.6.3' );
define( 'PIGEONWP_DIR', trailingslashit( dirname( __DIR__ ) ) );
define( 'PIGEONWP_URL', trailingslashit( plugins_url( '', PIGEONWP_DIR . 'pigeon.php' ) ) );
define( 'PIGEONWP_BASENAME', plugin_basename( PIGEONWP_DIR . 'pigeon.php' ) );

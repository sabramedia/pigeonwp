<?php
/**
 * Configs used throughout the plugin.
 *
 * @since 1.6
 *
 * @package Pigeon\Plugin\PigeonWP
 */

namespace PigeonWP;

define( 'PIGEONWP_VERSION', '1.6' );
define( 'PIGEONWP_DIR', trailingslashit( dirname( __DIR__ ) ) );
define( 'PIGEONWP_URL', trailingslashit( plugins_url( 'pigeonwp', PIGEONWP_DIR ) ) );
define( 'PIGEONWP_BASENAME', plugin_basename( PIGEONWP_DIR . 'pigeonwp.php' ) );

<?php
/**
 * Configs used throughout the plugin.
 *
 * @since 1.6
 *
 * @package Pigeon\Plugin\PigeonWP
 */

namespace Pigeon\Plugin\PigeonWP;

define( 'PIGEONWP_DIR', trailingslashit( dirname( __DIR__ ) ) );
define( 'PIGEONWP_URL', trailingslashit( plugins_url( 'pigeonwp', PIGEONWP_DIR ) ) );

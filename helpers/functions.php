<?php
/**
 * Helper functions.
 *
 * @package PigeonWP
 */

namespace PigeonWP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get plugin settings.
 *
 * @since 1.6
 *
 * @return array
 */
function get_plugin_settings() {
	return Bootstrap::get_instance()->get_container( 'settings' )->get_settings();
}

/**
 * Check if the paywall is enabled.
 *
 * @since 1.6
 *
 * @return boolean
 */
function is_paywall_enabled() {
	$settings = get_plugin_settings();

	$demo = ! empty( $settings['pigeon_demo'] ) ? $settings['pigeon_demo'] : 0;

	if ( $demo && ! current_user_can( 'activate_plugins' ) ) {
		return false;
	}

	return true;
}

/**
 * Get plugin URL with path.
 *
 * @since 1.6.3
 *
 * @param string $path The path to add.
 *
 * @return string
 */
function get_plugin_url( $path = '' ) {
	return ! empty( $path ) ? PIGEONWP_URL . ltrim( $path, '/' ) : PIGEONWP_URL;
}

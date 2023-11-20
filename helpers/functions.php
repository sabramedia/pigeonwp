<?php
/**
 * Helper functions.
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Get plugin settings.
 *
 * @return array
 */
function get_plugin_settings() {
	return Bootstrap::get_instance()->get_container( 'settings' )->get_settings();
}

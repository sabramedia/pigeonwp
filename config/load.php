<?php
/**
 * Defines the available classes to be loaded.
 *
 * @since 1.6
 *
 * @package PigeonWP
 */

namespace PigeonWP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$classes = array(
	'PigeonWP\\Access_Controls',
	'PigeonWP\\Access_Tags',
	'PigeonWP\\Admin',
	'PigeonWP\\Editor_Panel',
	'PigeonWP\\PDF',
	'PigeonWP\\Pigeon',
	'PigeonWP\\Protect',
	'PigeonWP\\RSS',
	'PigeonWP\\Settings',
	'PigeonWP\\Shortcodes',
	'PigeonWP\\Sticky_Bar',
);

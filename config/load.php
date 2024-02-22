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
	'PigeonWP\\Admin',
	'PigeonWP\\PDF',
	'PigeonWP\\Pigeon',
	'PigeonWP\\Protect',
	'PigeonWP\\RSS',
	'PigeonWP\\Settings',
	'PigeonWP\\Shortcodes',
	'PigeonWP\\Sticky_Bar',
);

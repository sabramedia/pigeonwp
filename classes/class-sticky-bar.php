<?php
/**
 * Sticky bar.
 *
 * @since 1.6
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Class Sticky_Bar
 *
 * Show a sticky bar on the page.
 *
 * @since 1.6
 */
class Sticky_Bar {

	/**
	 * Handle for the Sticky CSS.
	 */
	const CSS_HANDLE = 'pigeon_sticky';

	/**
	 * Handle for the Sticky JS.
	 */
	const JS_HANDLE = 'pigeon_sticky';

	/**
	 * Hooks
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function hooks() {
		$settings = get_plugin_settings();

		if ( ! empty( $settings['pigeon_content_pref_category'] ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( self::CSS_HANDLE, PIGEONWP_DIR . 'src/sticky.css', array(), PIGEONWP_VERSION );
		wp_enqueue_script( self::JS_HANDLE, PIGEONWP_DIR . 'src/sticky.js', array( 'jquery' ), PIGEONWP_VERSION, false );
	}
}
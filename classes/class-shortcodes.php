<?php
/**
 * Shortcodes.
 *
 * @since 1.6
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Class Shortcodes
 *
 * Implment plugin shortcodes.
 *
 * @since 1.6
 */
class Shortcodes {

	/**
	 * Hooks.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function hooks() {
		add_shortcode( 'pigeon_protect', array( $this, 'protect_shortcode' ) );
		add_shortcode( 'pigeon_display_when', array( $this, 'display_shortcode' ) );
		add_shortcode( 'pigeon_content_expires', array( $this, 'content_expires_shortcode' ) );
	}

	/**
	 * Protect shortcode.
	 *
	 * @since 1.4.8
	 *
	 * @param array       $atts Shortcode attributes.
	 * @param null|string $content The shortcode content.
	 * @return string
	 */
	public function protect_shortcode( $atts = array(), $content = null ) {
		// Run shortcode parser recursively.
		$content  = do_shortcode( $content );
		$content  = '<div class="pigeon-remove">' . $content . '</div>';
		$content .= '<div class="pigeon-context-promotion" style="display:none;"><p>' . esc_html__( 'This page is available to subscribers.', 'pigeonwp' ) . ' <a href="#" class="pigeon-open">' . esc_html__( 'Click here to sign in or get access', 'pigeonwp' ) . '</a>.</p></div>';

		return apply_filters( 'the_content', $content );
	}

	/**
	 * Pigeon display block and attribute conditions.
	 *
	 * @since 1.4.8
	 *
	 * @param array       $atts Shortcode attributes.
	 * @param null|string $content The shortcode content.
	 * @param string      $tag The shortcode tag.
	 * @return string
	 */
	public function display_shortcode( $atts = array(), $content = null, $tag = '' ) {
		// Normalize attribute keys, lowercase.
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		// Override default attributes with user attributes.
		$pigeon_atts = shortcode_atts(
			array(
				'access' => 'disabled',
			),
			$atts,
			$tag
		);

		// Handle shortcode differently base on the paywall mode.
		$o = '';

		// Develop attr string.
		$attr_str = '';

		foreach ( $pigeon_atts as $key => $val ) {
			$attr_str .= ' data-' . $key . '="' . $val . '"';
		}

		// Run shortcode parser recursively.
		// Handle display conditions from the js plugin.
		$o .= '<div class="pigeon-message" style="display:none;"' . $attr_str . '>';
		if ( ! is_null( $content ) ) {
			// Run shortcode parser recursively.
			$content = do_shortcode( $content );

			// Secure output by executing the_content filter hook on $content.
			$o .= apply_filters( 'the_content', $content );
		}
		$o .= '</div>';

		return $o;
	}

	/**
	 * Content expires shortcode.
	 *
	 * @since 1.4.8
	 *
	 * @param array       $atts Shortcode attributes.
	 * @param null|string $content The shortcode content.
	 * @param string      $tag The shortcode tag.
	 * @return string
	 */
	public function content_expires_shortcode( $atts = array(), $content = null, $tag = '' ) {
		// Normalize attribute keys, lowercase.
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		$pigeon_atts = shortcode_atts(
			array(
				'format' => 'F j, Y g:i A T',
			),
			$atts,
			$tag
		);

		// Run shortcode parser recursively.
		$content = '<div class="pigeon-content-expires" data-format="' . $pigeon_atts['format'] . '"></div>';

		return $content;
	}
}

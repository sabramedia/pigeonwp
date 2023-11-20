<?php
/**
 * Protect content with paywall.
 *
 * @since 1.6
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Class Protect
 *
 * Filters content for paywall.
 *
 * @since 1.6
 */
class Protect {

	/**
	 * Hooks
	 *
	 * @since 1.6
	 * @return void
	 */
	public function hooks() {
		add_filter( 'the_content', array( $this, 'inject_divs' ) );
	}

	/**
	 * Inject Pigeon protection divs into the content.
	 *
	 * @since 1.6
	 *
	 * @param string $content The post content.
	 *
	 * @return string
	 */
	public function inject_divs( $content ) {
		if ( ! is_front_page() && ( is_singular() || is_page() ) ) {
			$before_div     = apply_filters( 'pigeon_before_div', '', array() );
			$before_content = apply_filters( 'pigeon_before_content', '', array() );
			$after_content  = apply_filters( 'pigeon_after_content', '', array() );
			$after_div      = apply_filters( 'pigeon_after_div', '', array() );

			$settings = get_plugin_settings();
			$p_html   = '';

			if ( $settings['pigeon_paywall_content_display'] > 0 ) {
				$p_html = ' data-pn="' . (int) $settings['pigeon_paywall_content_display'] . '"';
			}

			$content = $before_div . '<div class="pigeon-remove"' . $p_html . '>' . $before_content . $content . $after_content . '</div>' . $after_div;
		}

		return $content;
	}
}

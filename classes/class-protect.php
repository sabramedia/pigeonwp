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
		$settings = get_plugin_settings();

		if ( ! is_paywall_enabled() ) {
			return;
		}

		$default    = array(
			'post',
			'page',
		);
		$post_types = ! empty( $settings['pigeon_content_post_types'] ) ? $settings['pigeon_content_post_types'] : $default;

		if ( ! is_front_page() && is_singular( $post_types ) ) {
			$before_div     = apply_filters( 'pigeon_before_div', '', array() );
			$before_content = apply_filters( 'pigeon_before_content', '', array() );
			$after_content  = apply_filters( 'pigeon_after_content', '', array() );
			$after_div      = apply_filters( 'pigeon_after_div', '', array() );

			$settings = get_plugin_settings();
			$p_html   = '';

			if ( ! empty( $settings['pigeon_paywall_content_display'] ) && $settings['pigeon_paywall_content_display'] > 0 ) {
				$p_html = ' data-pn="' . (int) $settings['pigeon_paywall_content_display'] . '"';
			}

			$content = $before_div . '<div class="pigeon-remove"' . $p_html . '>' . $before_content . $content . $after_content . '</div>' . $after_div;

			// Check for custom CTA.
			if ( ! empty( $settings['pigeon_cta_message'] ) ) {
				$cta_message = $settings['pigeon_cta_message'];
			} else {
				$cta_message = apply_filters( 'pigeon_cta_message', __( 'This page is available to subscribers. Click here to sign in or get access.', 'pigeon' ), array() );
			}

			$pigeon_cta = apply_filters( 'pigeon_cta', '<div class="pigeon-context-promotion" style="display:none;"><p class="pigeon-cta"><a href="#" class="pigeon-open">' . $cta_message . '</a></p></div>', array() );

			$content .= $pigeon_cta;
		}

		return $content;
	}
}

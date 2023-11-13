<?php
/**
 * RSS implementation.
 *
 * @since 1.6
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Class RSS
 *
 * Filters for the RSS feed.
 *
 * @since 1.6
 */
class RSS {

	/**
	 * Hooks.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'rss2_head', array( $this, 'parse_pigeon_access_rss' ) );
		add_action( 'rss2_item', array( $this, 'add_pigeon_field_to_rss' ) );
		add_action( 'rss_item', array( $this, 'add_pigeon_field_to_rss' ) );
	}

	/**
	 * Handle access to RSS feeds.
	 *
	 * @since 1.5.8
	 *
	 * @return void
	 */
	public function parse_pigeon_access_rss() {
		$url_array = array();
		global $wp_query;

		foreach ( $wp_query->get_posts() as $post ) {
			$url_array[ $post->ID ] = get_permalink( $post->ID );
		}

		$settings = Bootstrap::get_instance()->get_container( 'settings' )->get_settings();

		$pigeon_subdomain = '';
		if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
			$pigeon_subdomain = $settings['pigeon_subdomain'] ? str_replace( array( 'https://', 'http://' ), '', $settings['pigeon_subdomain'] ) : 'my.' . str_replace( 'www.', '', sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) );
		}

		$response = wp_remote_post(
			'https://' . $pigeon_subdomain . '/action/public/vo/pigeon-server',
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => array(
					'action' => 'check_url',
					'json'   => wp_json_encode( $url_array ),
				),
			)
		);

		$response = json_decode( $response, true );

		if ( $response ) {
			echo "\t<pigeonServer>\n";
			foreach ( $url_array as $key => $url ) {
				$access = (int) $response[ $key ];
				echo "\t\t" . '<item id="' . esc_attr( $key ) . '" access="' . esc_attr( $access ) . '">' . esc_html( $url ) . "</item>\n";
			}
			echo "\t</pigeonServer>\n";
		}
	}

	/**
	 * Add Pigeon access field to the feed.
	 *
	 * @since 1.5.8
	 *
	 * @return void
	 */
	public function add_pigeon_field_to_rss() {
		global $post, $response;

		$pigeon_meta_values = get_pigeon_post_meta( $post->ID );
		if ( array_key_exists( 'content_access', $pigeon_meta_values ) ) {
			$pigeon_access = $pigeon_meta_values['content_access'];
		} else {
			// If the content_access is not set locally then grab the Pigeon Server version.
			$pigeon_access = isset( $response[ $post->ID ] ) ? $response[ $post->ID ] : 1; // Default to public if not set.
		}

		echo "\n\t\t<pigeonAccess>" . esc_html( $pigeon_access ) . "</pigeonAccess>\n";
	}
}

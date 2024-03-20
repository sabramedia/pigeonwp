<?php
/**
 * WP Pigeon
 *
 * @package   PigeonWP
 * @author    Pigeon <support@pigeon.io>
 * @license   GPL-2.0+
 * @link      https://pigeon.io
 * @copyright 2014 Sabramedia
 */

namespace PigeonWP;

/**
 * Class Pigeon.
 *
 * The core class for the plugin.
 *
 * @since 1.0
 */
class Pigeon {
	/**
	 * Unique identifier for JS script.
	 *
	 * @since   1.6
	 *
	 * @var     string
	 */
	protected $js_handle = 'pigeon';

	/**
	 * Run the plugin hooks.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function hooks() {
		// Load JS.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Initialize the JS.
	 *
	 * @since    1.6
	 * @return   void
	 */
	public function enqueue_scripts() {
		$settings = get_plugin_settings();

		if ( ! empty( $settings['pigeon_subdomain'] ) ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( $this->js_handle, '//' . $settings['pigeon_subdomain'] . '/c/assets/pigeon.js', array( 'jquery' ), PIGEONWP_VERSION, array( 'in_footer' => false ) );
		}

		$script  = $this->get_pigeon_class_js( $settings );
		$script .= $this->get_paywall_js( $settings );

		wp_add_inline_script( $this->js_handle, $script, 'after' );
	}

	/**
	 * Get Pigeon Class JS.
	 *
	 * @since 1.6
	 * @param array $settings The plugin settings.
	 * @return string
	 */
	public function get_pigeon_class_js( $settings ) {
		$http_host = '';
		if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
			$http_host = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
		}

		return "
		var Pigeon = new PigeonClass({
			subdomain:'" . $settings['pigeon_subdomain'] . "',
			fingerprint:true," . (
				// If primary domain is not found in the subdomain, then use iFrame for SSO IDP.
				strstr( $settings['pigeon_subdomain'], str_replace( 'www.', '', $http_host ) ) === false ? "idp:true,\n\t\t\t" : ''
			) . '
			cid: null,
			cha: null
		});
		';
	}

	/**
	 * Get the paywall JS.
	 *
	 * @since 1.6
	 *
	 * @param array $settings The plugin settings.
	 * @param bool  $init_widget Initiate the widget.
	 *
	 * @return string
	 */
	public function get_paywall_js( $settings, $init_widget = true ) {
		if ( empty( $settings['pigeon_paywall_interrupt'] ) ) {
			$settings['pigeon_paywall_interrupt'] = 3;
		}

		switch ( $settings['pigeon_paywall_interrupt'] ) {
			case '1':
				$paywall_iterrupt = 1;
				break;
			case '2':
				$paywall_iterrupt = 0;
				break;
			case '3':
			default:
				$paywall_iterrupt = 'modal';
				break;
		}

		$page_values  = $this->get_single_page_values( $settings );
		$is_page_free = $page_values['content_access'];

		// Don't count the 404 pages.
		if ( is_404() ) {
			$is_page_free = 1;
		}

		$js = '
			Pigeon.paywall({
				redirect:' . wp_json_encode( $paywall_iterrupt ) . ',
				free:' . wp_json_encode( $is_page_free ) . ',
				contentId:' . wp_json_encode( $page_values['content_id'] ) . ',
				contentTitle:' . wp_json_encode( $page_values['content_title'] ) . ',
				contentDate:' . wp_json_encode( $page_values['content_date'] ) . ',
				contentPrice:' . wp_json_encode( $page_values['content_price'] ) . ',
				contentValue:' . wp_json_encode( $page_values['content_value'] ) . ',
				contentPrompt:' . wp_json_encode( $page_values['content_prompt'] ) . ',
				wpPostType:' . wp_json_encode( $page_values['wp_post_type'] ) . '
			});';

		if ( $init_widget ) {
			$js .= 'Pigeon.widget.status();';
		}

		return $js;
	}

	/**
	 * Get the page values for a single page/post request.
	 *
	 * @since 1.6
	 * @param array $settings The plugin settings.
	 * @return array
	 */
	protected function get_single_page_values( $settings ) {
		$values = array(
			'content_id'     => 0,
			'content_title'  => '',
			'content_date'   => '',
			'content_access' => 0,
			'content_price'  => 0,
			'content_value'  => 0,
			'content_prompt' => 0,
			'wp_post_type'   => '',
		);

		if ( is_admin() ) {
			return $values;
		}

		$request_uri = '';

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		// Avoid asset requests.
		foreach ( array( '.css', '.js', '.woff', '.eot', '.ttf', '.svg', '.png', '.jpg', '.gif', '.cur', 'css?' ) as $asset ) {
			if ( strpos( basename( $request_uri ), $asset ) !== false ) {
				return $values;
			}
		}

		// Get our content access settings.
		if ( is_singular() ) {
			global $post;

			$values['content_id']     = $post->ID;
			$values['content_title']  = $post->post_title;
			$values['content_date']   = $post->post_date_gmt;
			$values['content_access'] = get_post_meta( $post->ID, '_wp_pigeon_content_access', true );
			$values['wp_post_type']   = $post->post_type;

			// Send zero dollar if the value meter is disabled.
			$values['content_price'] = ! empty( $settings['pigeon_content_value_pricing'] ) ? get_post_meta( $post->ID, '_wp_pigeon_content_price', true ) : 0;

			// Send zero value if the value meter is disabled.
			$values['content_value']  = ! empty( $settings['pigeon_content_value_meter'] ) ? (int) get_post_meta( $post->ID, '_wp_pigeon_content_value', true ) : 0;
			$values['content_prompt'] = (int) get_post_meta( $post->ID, '_wp_pigeon_content_prompt', true );
		}

		return $values;
	}
}

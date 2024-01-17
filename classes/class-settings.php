<?php
/**
 * Plugin settings.
 *
 * @since 1.6
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Class Settings
 *
 * Settings needed for the plugin.
 *
 * @since 1.6
 */
class Settings {

	/**
	 * The key for storing settings.
	 *
	 * @since 1.6
	 */
	const SETTINGS_KEY = 'wp_pigeon_settings';

	/**
	 * Hooks.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function hooks() {
		// Register the settings.
		add_action( 'admin_menu', array( $this, 'plugin_settings_init' ) );
	}

	/**
	 * Return settings
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = get_option( self::SETTINGS_KEY );

		if ( ! empty( $settings ) ) {
			return $settings;
		}

		return array();
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.1.0
	 */
	public function plugin_settings_init() {
		register_setting( 'plugin_options', self::SETTINGS_KEY );

		// Register our sections.
		add_settings_section(
			'settings_section_basic',
			__( 'Basic Configuration', 'pigeonwp' ),
			array( $this, 'settings_section_basic_callback' ),
			'plugin_options'
		);

		add_settings_section(
			'settings_section_api',
			__( 'API Connection', 'pigeonwp' ),
			array( $this, 'settings_section_api_callback' ),
			'plugin_options'
		);

		add_settings_section(
			'settings_section_content',
			__( 'Content', 'pigeonwp' ),
			array( $this, 'settings_section_content_callback' ),
			'plugin_options'
		);

		// Register our fields.
		add_settings_field(
			'pigeon_subdomain',
			__( 'Pigeon Subdomain', 'pigeonwp' ),
			array( $this, 'setting_pigeon_subdomain_render' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_paywall_sticky',
			__( 'Sticky Bar', 'pigeonwp' ),
			array( $this, 'setting_pigeon_paywall_sticky' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_paywall_content_display',
			__( 'Content Display', 'pigeonwp' ),
			array( $this, 'setting_pigeon_paywall_content_display' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_paywall_interrupt',
			__( 'Paywall Interrupt', 'pigeonwp' ),
			array( $this, 'setting_pigeon_paywall_interrupt_render' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_cta_message',
			__( 'Paywall CTA Message', 'pigeonwp' ),
			array( $this, 'setting_pigeon_paywall_cta_render' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_content_post_types',
			__( 'Post Types', 'pigeonwp' ),
			array( $this, 'setting_pigeon_content_post_type_render' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_api_user',
			__( 'User', 'pigeonwp' ),
			array( $this, 'setting_pigeon_api_user_render' ),
			'plugin_options',
			'settings_section_api'
		);

		add_settings_field(
			'pigeon_api_secret_key',
			__( 'Private Key', 'pigeonwp' ),
			array( $this, 'setting_pigeon_api_secret_key_render' ),
			'plugin_options',
			'settings_section_api'
		);

		add_settings_field(
			'pigeon_content_value_pricing',
			__( 'Pricing Value', 'pigeonwp' ),
			array( $this, 'setting_pigeon_content_value_pricing_render' ),
			'plugin_options',
			'settings_section_content'
		);

		add_settings_field(
			'pigeon_content_value_meter',
			__( 'Value Meter', 'pigeonwp' ),
			array( $this, 'setting_pigeon_content_value_meter_render' ),
			'plugin_options',
			'settings_section_content'
		);

		add_settings_field(
			'pigeon_content_value',
			__( 'Credit Value', 'pigeonwp' ),
			array( $this, 'setting_pigeon_content_value_render' ),
			'plugin_options',
			'settings_section_content'
		);

		add_settings_field(
			'pigeon_content_pref_category',
			__( 'Category Preferences', 'pigeonwp' ),
			array( $this, 'setting_pigeon_content_category_render' ),
			'plugin_options',
			'settings_section_api'
		);
	}

	/**
	 * Basic section settings callback.
	 *
	 * @since    1.1.0
	 */
	public function settings_section_basic_callback() {}

	/**
	 * API Section settings callback.
	 *
	 * @since    1.1.0
	 */
	public function settings_section_api_callback() {}

	/**
	 * Content Section settings callback.
	 *
	 * @since    1.4.0
	 */
	public function settings_section_content_callback() {
		esc_html_e( 'Only used when content value needs to be set in WordPress and passed to Pigeon.', 'pigeonwp' );
	}

	/**
	 * Pigeon subdomain callback.
	 *
	 * @since    1.1.0
	 */
	public function setting_pigeon_subdomain_render() {
		$options   = $this->get_settings();
		$subdomain = ! empty( $options['pigeon_subdomain'] ) ? $options['pigeon_subdomain'] : '';
		?>
		<input type="text" name="wp_pigeon_settings[pigeon_subdomain]" value="<?php echo esc_attr( $subdomain ); ?>">
		<p class="description"><?php esc_html_e( 'Defines the subdomain used for Pigeon.', 'pigeonwp' ); ?></p>
		<?php
	}

	/**
	 * Pigeon redirect callback.
	 *
	 * @since    1.1.0
	 */
	public function setting_pigeon_redirect_render() {
		$options  = $this->get_settings();
		$redirect = ! empty( $options['pigeon_redirect'] ) ? $options['pigeon_redirect'] : '';

		$html  = '<input type="radio" id="redirect_enabled" name="wp_pigeon_settings[pigeon_redirect]" value="1"' . checked( 1, $redirect, false ) . '/>';
		$html .= '<label for="redirect_enabled">' . esc_html__( 'Enabled', 'pigeonwp' ) . '</label><br />';
		$html .= '<input type="radio" id="redirect_disabled" name="wp_pigeon_settings[pigeon_redirect]" value="2"' . checked( 2, $redirect, false ) . '/>';
		$html .= '<label for="redirect_disabled">' . esc_html__( 'Disabled', 'pigeonwp' ) . '</label>';
		$html .= '<p class="description">' . esc_html__( 'Determines whether the plugin does the automatic reroute or stays on the page.', 'pigeonwp' ) . '</p>';

		echo $html; // @phpcs:ignore
	}

	/**
	 * Pigeon Paywall interrupt type.
	 *
	 * @since 1.6
	 */
	public function setting_pigeon_paywall_sticky() {
		$options = $this->get_settings();
		$sticky  = ! empty( $options['pigeon_paywall_sticky'] ) ? $options['pigeon_paywall_sticky'] : 0;
		?>
		<input type="radio" id="pigeon_paywall_sticky" name="wp_pigeon_settings[pigeon_paywall_sticky]" value="1"<?php checked( 1, $sticky, true ); ?>/>
		<label for="paywall_server"><?php esc_html_e( 'Show', 'pigeonwp' ); ?></label><br />
		<input type="radio" id="paywall_interrupt_modal" name="wp_pigeon_settings[pigeon_paywall_sticky]" value="0"<?php checked( 0, $sticky, true ); ?>/>
		<label for="paywall_js"><?php esc_html_e( 'Hide', 'pigeonwp' ); ?></label>
		<p class="description"><?php esc_html_e( 'Show a sticky bar on each page with paywall information.', 'pigeonwp' ); ?></p>
		<?php
	}

	/**
	 * Pigeon content display settings.
	 *
	 * @since 1.6
	 */
	public function setting_pigeon_paywall_content_display() {
		$options         = $this->get_settings();
		$content_display = ! empty( $options['pigeon_paywall_content_display'] ) ? $options['pigeon_paywall_content_display'] : 0;
		?>
		<select name="wp_pigeon_settings[pigeon_paywall_content_display]">
			<option name="0"<?php selected( 0, $content_display, true ); ?>><?php esc_html_e( 'None', 'pigeonwp' ); ?></option>
			<?php for ( $i = 1; $i <= 20; $i++ ) : ?>
				<option name="<?php echo esc_attr( $i ); ?>"<?php selected( $i, $content_display, true ); ?>><?php echo esc_html( $i ); ?></option>
			<?php endfor; ?>
		</select>
		<p class="description"><?php esc_html_e( 'How many paragraphs do you want to show of a protected article?', 'pigeonwp' ); ?></p>
		<?php
	}

	/**
	 * Pigeon Paywall interrupt type.
	 *
	 * @since    1.3.0
	 */
	public function setting_pigeon_paywall_interrupt_render() {
		$options   = $this->get_settings();
		$interrupt = ! empty( $options['pigeon_paywall_interrupt'] ) ? $options['pigeon_paywall_interrupt'] : '';

		$html  = '<input type="radio" id="paywall_interrupt_redirect" name="wp_pigeon_settings[pigeon_paywall_interrupt]" value="1"' . checked( 1, $interrupt, false ) . '/>';
		$html .= '<label for="paywall_server">' . esc_html__( 'Redirect', 'pigeonwp' ) . '</label><br />';
		$html .= '<input type="radio" id="paywall_interrupt_modal" name="wp_pigeon_settings[pigeon_paywall_interrupt]" value="3"' . checked( 3, $interrupt, false ) . '/>';
		$html .= '<label for="paywall_js">' . esc_html__( 'Modal Popup', 'pigeonwp' ) . '</label><br />';
		$html .= '<input type="radio" id="paywall_interrupt_custom" name="wp_pigeon_settings[pigeon_paywall_interrupt]" value="2"' . checked( 2, $interrupt, false ) . '/>';
		$html .= '<label for="paywall_js">' . esc_html__( 'Custom', 'pigeonwp' ) . '</label>';
		$html .= '<p class="description">' . esc_html__( 'Redirect respects paywall rules. Modal uses the default Pigeon modal. Custom allows you to take your own actions. Refer to documentation on how to do this.', 'pigeonwp' ) . '</p>';

		echo $html; // @phpcs:ignore
	}

	/**
	 * Pigeon Paywall cta message.
	 *
	 * @since    1.3.0
	 */
	public function setting_pigeon_paywall_cta_render() {
		$options     = $this->get_settings();
		$cta_message = ! empty( $options['pigeon_cta_message'] ) ? $options['pigeon_cta_message'] : __( 'This page is available to subscribers. Click here to sign in or get access.', 'pigeonwp' );
		?>
		<textarea name="wp_pigeon_settings[pigeon_cta_message]" class="large-text" rows="3"><?php echo wp_kses_post( $cta_message ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Message to show when an article is protected behind the paywall.', 'pigeonwp' ); ?></p>
		<?php
	}

	/**
	 * API user callback.
	 *
	 * @since    1.1.0
	 */
	public function setting_pigeon_api_user_render() {
		$options  = $this->get_settings();
		$api_user = ! empty( $options['pigeon_api_user'] ) ? $options['pigeon_api_user'] : '';
		?>
		<input type="text" name="wp_pigeon_settings[pigeon_api_user]" value="<?php echo esc_attr( $api_user ); ?>">
		<?php
	}

	/**
	 * API secret key callback.
	 *
	 * @since    1.1.0
	 */
	public function setting_pigeon_api_secret_key_render() {
		$options = $this->get_settings();
		$secret  = ! empty( $options['pigeon_api_secret_key'] ) ? $options['pigeon_api_secret_key'] : '';
		?>
		<input type="text" name="wp_pigeon_settings[pigeon_api_secret_key]" value="<?php echo esc_attr( $secret ); ?>">
		<?php
		if ( ! empty( $options['pigeon_api_user'] ) && ! empty( $options['pigeon_api_secret_key'] ) ) {
			try {
				require_once PIGEONWP_DIR . 'sdk/Pigeon.php';

				\Pigeon_Configuration::clientId( $options['pigeon_api_user'] );
				\Pigeon_Configuration::apiKey( $options['pigeon_api_secret_key'] );
				\Pigeon_Configuration::pigeonDomain( $options['pigeon_subdomain'] );

				// Send the category array.
				$pigeon_sdk = new \Pigeon();

				// Make a call to see if it works.
				$pigeon_sdk->get( '', array() );
			} catch ( \Exception $e ) {
				echo '<p style="color:#ca4a1f">' . esc_html__( 'There is a connectivity issue. Make sure the Pigeon API credentials are correct. This plugin uses cURL. Please make sure this is enabled in order for the direct API to work.', 'pigeonwp' ) . '</p>';
			}
		}
	}

	/**
	 * Content value pricing on or off.
	 *
	 * @since    1.4.7
	 */
	public function setting_pigeon_content_value_pricing_render() {
		$options = $this->get_settings();
		$pricing = ! empty( $options['pigeon_content_value_pricing'] ) ? $options['pigeon_content_value_pricing'] : 2;

		$html  = '<input type="radio" id="value_pricing_enabled" class="pigeon-value-pricing" name="wp_pigeon_settings[pigeon_content_value_pricing]" value="1"' . checked( 1, $pricing, false ) . '/>';
		$html .= '<label for="value_pricing_enabled">' . esc_html__( 'Enabled', 'pigeonwp' ) . '</label><br />';
		$html .= '<input type="radio" id="value_pricing_disabled" class="pigeon-value-pricing" name="wp_pigeon_settings[pigeon_content_value_pricing]" value="2"' . checked( 2, $pricing, false ) . '/>';
		$html .= '<label for="value_pricing_disabled">' . esc_html__( 'Disabled', 'pigeonwp' ) . '</label>';

		echo $html; // @phpcs:ignore
	}

	/**
	 * Content value meter on or off.
	 *
	 * @since    1.4.0
	 */
	public function setting_pigeon_content_value_meter_render() {
		$options = $this->get_settings();
		$meter   = ! empty( $options['pigeon_content_value_meter'] ) ? $options['pigeon_content_value_meter'] : 2;

		$html  = '<input type="radio" id="value_meter_enabled" class="pigeon-value-meter" name="wp_pigeon_settings[pigeon_content_value_meter]" value="1"' . checked( 1, $meter, false ) . '/>';
		$html .= '<label for="value_meter_enabled">' . esc_html__( 'Enabled', 'pigeonwp' ) . '</label><br />';
		$html .= '<input type="radio" id="value_meter_disabled" class="pigeon-value-meter" name="wp_pigeon_settings[pigeon_content_value_meter]" value="2"' . checked( 2, $meter, false ) . '/>';
		$html .= '<label for="value_meter_disabled">' . esc_html__( 'Disabled', 'pigeonwp' ) . '</label>';

		echo $html; // @phpcs:ignore
	}

	/**
	 * Content value list.
	 *
	 * @since    1.4.0
	 */
	public function setting_pigeon_content_value_render() {
		$options = $this->get_settings();

		// Preset empty array if not set.
		if ( ! isset( $options['pigeon_content_value'] ) ) {
			$options['pigeon_content_value'] = array( '' );
		}

		foreach ( $options['pigeon_content_value'] as $option ) {
			?>
		<div class="pigeon-content-value-option">
			<input type='text' name='wp_pigeon_settings[pigeon_content_value][]' value='<?php echo esc_attr( $option ); ?>'>
			<button class="remove"><?php echo esc_html__( 'Remove', 'pigeonwp' ); ?></button>
		</div>
			<?php
		}
		?>
		<div class="pigeon-add-content-value">
			<button><?php echo esc_html__( 'Add New Value', 'pigeonwp' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Content value list.
	 *
	 * @since    1.4.0
	 */
	public function setting_pigeon_content_post_type_render() {
		$options = $this->get_settings();

		// Preset empty array if not set.
		if ( ! isset( $options['pigeon_content_post_types'] ) ) {
			$options['pigeon_content_value'] = array( '' );
		}

		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'names',
			'and'
		);

		$default = array(
			'post' => 'Post',
			'page' => 'Page',
		);

		$post_types = array_merge(
			$default,
			$post_types
		);

		if ( $post_types ) {
			$post_type_options = ! empty( $options['pigeon_content_post_types'] ) ? $options['pigeon_content_post_types'] : $default;

			foreach ( $post_types as $option ) {
				$checked = false;
				if ( in_array( $option, $post_type_options, true ) ) {
					$checked = true;
				}
				?>
				<div class="pigeon-content-post-type-option">
					<input type='checkbox' name='wp_pigeon_settings[pigeon_content_post_types][]' value='<?php echo esc_attr( $option ); ?>'<?php echo esc_attr( $checked ? ' checked' : '' ); ?> /> <?php echo esc_html( $option ); ?>
				</div>
				<?php
			}
			?>
			<p class="description"><?php esc_html_e( 'Enable the paywall on the following posts, pages and post types.', 'pigeonwp' ); ?></p>
			<?php
		} else {
			?>
			<div class="pigeon-add-post-type">
			<?php echo esc_html__( 'There are no custom post types available.', 'pigeonwp' ); ?>
		</div>
			<?php
		}
	}

	/**
	 * Content category preferences on or off.
	 *
	 * @since    1.5.9
	 */
	public function setting_pigeon_content_category_render() {
		$options       = $this->get_settings();
		$required_note = '';

		// This may be a bit non-standard, but if the page loads with the plugin enabled, then run an api call to enable the plugin.
		// Only run the following if the api keys are set.
		if ( ! empty( $options['pigeon_api_user'] ) && ! empty( $options['pigeon_api_secret_key'] ) ) {
			if ( ! empty( $_GET['settings-updated'] ) ) { // @phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$category_sync = Bootstrap::get_instance()->get_container( 'category-sync' );

				if ( array_key_exists( 'pigeon_content_pref_category', $options ) && 1 === (int) $options['pigeon_content_pref_category'] ) {
					$category_sync->pigeon_category_enable();
				} else {
					$category_sync->pigeon_category_disable();
				}
			}
		} else {
			$required_note = '<strong>' . esc_html__( 'Requires API User and Private Key from Settings > API in your Pigeon dashboard.', 'pigeonwp' ) . '</strong>';
		}

		$options['pigeon_content_pref_category'] = ! empty( $options['pigeon_content_pref_category'] ) ? $options['pigeon_content_pref_category'] : 2;

		$html  = '<input type="radio" id="category_pref_enabled" class="pigeon-content-pref-category" name="wp_pigeon_settings[pigeon_content_pref_category]" value="1"' . checked( 1, $options['pigeon_content_pref_category'], false ) . '/>';
		$html .= '<label for="category_pref_enabled">' . esc_html__( 'Enabled', 'pigeonwp' ) . '</label><br />';
		$html .= '<input type="radio" id="category_pref_disabled" class="pigeon-value-meter" name="wp_pigeon_settings[pigeon_content_pref_category]" value="2"' . checked( 2, $options['pigeon_content_pref_category'], false ) . '/>';
		$html .= '<label for="category_pref_disabled">' . esc_html__( 'Disabled', 'pigeonwp' ) . '</label>';
		$html .= '<p class="description">' . $required_note . esc_html__( 'Enable to send Post Categories to Pigeon. Registered users can choose which content categories they prefer.', 'pigeonwp' ) . '</p>';

		echo $html; // @phpcs:ignore
	}
}

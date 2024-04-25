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

		// Default settings on activation.
		add_action( 'activated_plugin', array( $this, 'set_defaults' ), 0 );

		// Ajax hander for saving subdomain.
		add_action( 'wp_ajax_pigeon_connect', array( $this, 'connect_pigeon' ) );
	}

	/**
	 * Return settings
	 *
	 * @since 1.6
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
	 * Add settings on activation.
	 *
	 * @since 1.6.2
	 * @param string $plugin The plugin being activated.
	 * @return void
	 */
	public function set_defaults( $plugin ) {
		if ( PIGEONWP_BASENAME !== $plugin ) {
			return;
		}

		$settings = get_plugin_settings();

		if ( ! empty( $settings ) ) {
			return;
		}

		$settings = array(
			'pigeon_demo' => 1,
		);

		update_option( self::SETTINGS_KEY, $settings );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.1.0
	 */
	public function plugin_settings_init() {
		register_setting( 'plugin_options', self::SETTINGS_KEY );

		// If the user has not entered a subdomain, give instructions for setting up an account.
		$settings = get_plugin_settings();

		if ( empty( $settings['pigeon_subdomain'] ) ) {
			add_settings_section(
				'settings_section_installation',
				__( 'Get Started', 'pigeon' ),
				array( $this, 'settings_section_installation_callback' ),
				'plugin_options'
			);

			if ( empty( $_GET['configure'] ) ) { // @phpcs:ignore
				return;
			}
		}

		// Register our sections.
		add_settings_section(
			'settings_section_basic',
			__( 'Basic Configuration', 'pigeon' ),
			array( $this, 'settings_section_basic_callback' ),
			'plugin_options'
		);

		// Register our fields.
		add_settings_field(
			'pigeon_subdomain',
			__( 'Pigeon Subdomain', 'pigeon' ),
			array( $this, 'setting_pigeon_subdomain_render' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_demo',
			__( 'Demo Mode', 'pigeon' ),
			array( $this, 'setting_pigeon_demo_render' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_section(
			'settings_section_content',
			__( 'Content', 'pigeon' ),
			array( $this, 'settings_section_content_callback' ),
			'plugin_options'
		);

		add_settings_field(
			'pigeon_paywall_sticky',
			__( 'Sticky Bar', 'pigeon' ),
			array( $this, 'setting_pigeon_paywall_sticky' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_paywall_content_display',
			__( 'Content Display', 'pigeon' ),
			array( $this, 'setting_pigeon_paywall_content_display' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_paywall_interrupt',
			__( 'Paywall Interrupt', 'pigeon' ),
			array( $this, 'setting_pigeon_paywall_interrupt_render' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_cta_message',
			__( 'Paywall CTA Message', 'pigeon' ),
			array( $this, 'setting_pigeon_paywall_cta_render' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_content_post_types',
			__( 'Post Types', 'pigeon' ),
			array( $this, 'setting_pigeon_content_post_type_render' ),
			'plugin_options',
			'settings_section_basic'
		);

		add_settings_field(
			'pigeon_content_pdf_paywall',
			__( 'PDF Paywall', 'pigeon' ),
			array( $this, 'setting_pigeon_pdf_paywall' ),
			'plugin_options',
			'settings_section_content'
		);

		add_settings_field(
			'pigeon_content_pdf_index',
			__( 'PDF Search Visibility', 'pigeon' ),
			array( $this, 'setting_pigeon_pdf_index' ),
			'plugin_options',
			'settings_section_content'
		);

		add_settings_field(
			'pigeon_content_value_pricing',
			__( 'Pricing Value', 'pigeon' ),
			array( $this, 'setting_pigeon_content_value_pricing_render' ),
			'plugin_options',
			'settings_section_content'
		);

		add_settings_field(
			'pigeon_content_value_meter',
			__( 'Value Meter', 'pigeon' ),
			array( $this, 'setting_pigeon_content_value_meter_render' ),
			'plugin_options',
			'settings_section_content'
		);

		add_settings_field(
			'pigeon_content_value',
			__( 'Credit Value', 'pigeon' ),
			array( $this, 'setting_pigeon_content_value_render' ),
			'plugin_options',
			'settings_section_content'
		);
	}

	/**
	 * Installation section callback.
	 *
	 * @since    1.6.1
	 */
	public function settings_section_installation_callback() {
		?>
		<p>
			<?php esc_html_e( 'To get started, connect your site to a Pigeon account or register a new Pigeon account.', 'pigeon' ); ?>
			<a href="<?php echo esc_url( admin_url( 'options-general.php?page=pigeon&configure=1' ) ); ?>"><?php esc_html_e( 'Configure manually instead?', 'pigeon' ); ?></a>
		</p>
		<input type="button" class="button button-primary" value="<?php esc_attr_e( 'Connect to Pigeon', 'pigeon' ); ?>" onclick="window.open( 'https://pigeon.io/cmc/register?src=wp&origin=<?php echo esc_url_raw( get_site_url() ); ?>', '_blank', 'location=yes,height=720,width=720' );">
		<script>
			function pigeonconnect( data ) {
				if ( data.subdomain != undefined ) {
					jQuery.ajax({
						type: 'post',
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						data: {
							nonce: '<?php echo esc_attr( wp_create_nonce( 'pigeon-connect-nonce' ) ); ?>',
							action: 'pigeon_connect',
							subdomain: data.subdomain
						},
						success: function() {
							window.location = '<?php echo esc_url( admin_url( 'options-general.php?page=pigeon' ) ); ?>';
						}
					});
				}
			};

			window.addEventListener( 'message', function( e ) {
				if ( e.origin !== 'https://pigeon.io' ) {
					return false;
				}
				var input = JSON.parse( e.data );
				switch ( input.action ) {
					case 'pigeonconnect':
						pigeonconnect( input );
						break;
				}
			} );
		</script>
		<?php
	}

	/**
	 * Basic section settings callback.
	 *
	 * @since    1.1.0
	 */
	public function settings_section_basic_callback() {}

	/**
	 * Content Section settings callback.
	 *
	 * @since    1.4.0
	 */
	public function settings_section_content_callback() {}

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
		<p class="description"><?php esc_html_e( 'Defines the subdomain used for Pigeon.', 'pigeon' ); ?></p>
		<?php
	}

	/**
	 * Pigeon demo callback.
	 *
	 * @since    1.6.2
	 */
	public function setting_pigeon_demo_render() {
		$options = $this->get_settings();
		$demo    = ! empty( $options['pigeon_demo'] ) ? $options['pigeon_demo'] : 0;

		$html  = '<input type="radio" id="demo_enabled" name="wp_pigeon_settings[pigeon_demo]" value="1"' . checked( 1, $demo, false ) . '/>';
		$html .= '<label for="demo_enabled">' . esc_html__( 'Enabled', 'pigeon' ) . '</label><br />';
		$html .= '<input type="radio" id="demo_disabled" name="wp_pigeon_settings[pigeon_demo]" value="0"' . checked( 0, $demo, false ) . '/>';
		$html .= '<label for="demo_disabled">' . esc_html__( 'Disabled', 'pigeon' ) . '</label>';
		$html .= '<p class="description">' . esc_html__( 'For testing purposes - only administrators will see the paywall.', 'pigeon' ) . '</p>';

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
		<label for="paywall_server"><?php esc_html_e( 'Show', 'pigeon' ); ?></label><br />
		<input type="radio" id="paywall_interrupt_modal" name="wp_pigeon_settings[pigeon_paywall_sticky]" value="0"<?php checked( 0, $sticky, true ); ?>/>
		<label for="paywall_js"><?php esc_html_e( 'Hide', 'pigeon' ); ?></label>
		<p class="description"><?php esc_html_e( 'Show a sticky bar on each page with paywall information.', 'pigeon' ); ?></p>
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
			<option name="0"<?php selected( 0, $content_display, true ); ?>><?php esc_html_e( 'None', 'pigeon' ); ?></option>
			<?php for ( $i = 1; $i <= 20; $i++ ) : ?>
				<option name="<?php echo esc_attr( $i ); ?>"<?php selected( $i, $content_display, true ); ?>><?php echo esc_html( $i ); ?></option>
			<?php endfor; ?>
		</select>
		<p class="description"><?php esc_html_e( 'How many paragraphs do you want to show of a protected article?', 'pigeon' ); ?></p>
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
		$html .= '<label for="paywall_server">' . esc_html__( 'Redirect', 'pigeon' ) . '</label><br />';
		$html .= '<input type="radio" id="paywall_interrupt_modal" name="wp_pigeon_settings[pigeon_paywall_interrupt]" value="3"' . checked( 3, $interrupt, false ) . '/>';
		$html .= '<label for="paywall_js">' . esc_html__( 'Modal Popup', 'pigeon' ) . '</label><br />';
		$html .= '<input type="radio" id="paywall_interrupt_custom" name="wp_pigeon_settings[pigeon_paywall_interrupt]" value="2"' . checked( 2, $interrupt, false ) . '/>';
		$html .= '<label for="paywall_js">' . esc_html__( 'Custom', 'pigeon' ) . '</label>';
		$html .= '<p class="description">' . esc_html__( 'Redirect respects paywall rules. Modal uses the default Pigeon modal. Custom allows you to take your own actions. Refer to documentation on how to do this.', 'pigeon' ) . '</p>';

		echo $html; // @phpcs:ignore
	}

	/**
	 * Pigeon Paywall cta message.
	 *
	 * @since    1.3.0
	 */
	public function setting_pigeon_paywall_cta_render() {
		$options     = $this->get_settings();
		$cta_message = ! empty( $options['pigeon_cta_message'] ) ? $options['pigeon_cta_message'] : __( 'This page is available to subscribers. Click here to sign in or get access.', 'pigeon' );
		?>
		<textarea name="wp_pigeon_settings[pigeon_cta_message]" class="large-text" rows="3"><?php echo wp_kses_post( $cta_message ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Message to show when an article is protected behind the paywall.', 'pigeon' ); ?></p>
		<?php
	}

	/**
	 * Content value pricing on or off.
	 *
	 * @since    1.4.7
	 */
	public function setting_pigeon_content_value_pricing_render() {
		$options = $this->get_settings();
		$pricing = ! empty( $options['pigeon_content_value_pricing'] ) ? $options['pigeon_content_value_pricing'] : 0;

		$html  = '<input type="radio" id="value_pricing_enabled" class="pigeon-value-pricing" name="wp_pigeon_settings[pigeon_content_value_pricing]" value="1"' . checked( 1, $pricing, false ) . '/>';
		$html .= '<label for="value_pricing_enabled">' . esc_html__( 'Enabled', 'pigeon' ) . '</label><br />';
		$html .= '<input type="radio" id="value_pricing_disabled" class="pigeon-value-pricing" name="wp_pigeon_settings[pigeon_content_value_pricing]" value="0"' . checked( 0, $pricing, false ) . '/>';
		$html .= '<label for="value_pricing_disabled">' . esc_html__( 'Disabled', 'pigeon' ) . '</label>';
		$html .= '<p class="description">' . esc_html__( 'Only used when content value needs to be set in WordPress and passed to Pigeon.', 'pigeon' ) . '</p>';

		echo $html; // @phpcs:ignore
	}

	/**
	 * Content value meter on or off.
	 *
	 * @since    1.4.0
	 */
	public function setting_pigeon_content_value_meter_render() {
		$options = $this->get_settings();
		$meter   = ! empty( $options['pigeon_content_value_meter'] ) ? $options['pigeon_content_value_meter'] : 0;

		$html  = '<input type="radio" id="value_meter_enabled" class="pigeon-value-meter" name="wp_pigeon_settings[pigeon_content_value_meter]" value="1"' . checked( 1, $meter, false ) . '/>';
		$html .= '<label for="value_meter_enabled">' . esc_html__( 'Enabled', 'pigeon' ) . '</label><br />';
		$html .= '<input type="radio" id="value_meter_disabled" class="pigeon-value-meter" name="wp_pigeon_settings[pigeon_content_value_meter]" value="0"' . checked( 0, $meter, false ) . '/>';
		$html .= '<label for="value_meter_disabled">' . esc_html__( 'Disabled', 'pigeon' ) . '</label>';
		$html .= '<p class="description">' . esc_html__( 'Only used when content value needs to be set in WordPress and passed to Pigeon.', 'pigeon' ) . '</p>';

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
			<button class="remove"><?php echo esc_html__( 'Remove', 'pigeon' ); ?></button>
		</div>
			<?php
		}
		?>
		<div class="pigeon-add-content-value">
			<button><?php echo esc_html__( 'Add New Value', 'pigeon' ); ?></button>
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
			'post' => 'post',
			'page' => 'page',
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
					<input type='checkbox' name='wp_pigeon_settings[pigeon_content_post_types][]' value='<?php echo esc_attr( strtolower( $option ) ); ?>'<?php echo esc_attr( $checked ? ' checked' : '' ); ?> /> <?php echo esc_html( $option ); ?>
				</div>
				<?php
			}
			?>
			<p class="description"><?php esc_html_e( 'Enable the paywall on the following posts, pages and post types.', 'pigeon' ); ?></p>
			<?php
		} else {
			?>
			<div class="pigeon-add-post-type">
			<?php echo esc_html__( 'There are no custom post types available.', 'pigeon' ); ?>
		</div>
			<?php
		}
	}

	/**
	 * Block PDFs with a paywall.
	 *
	 * @since    1.6.0
	 */
	public function setting_pigeon_pdf_paywall() {
		$options = $this->get_settings();

		$options['pigeon_content_pdf_paywall'] = ! empty( $options['pigeon_content_pdf_paywall'] ) ? $options['pigeon_content_pdf_paywall'] : 0;

		$html  = '<input type="radio" id="pdf_paywall_enabled" class="pigeon-content-pdf-paywall" name="wp_pigeon_settings[pigeon_content_pdf_paywall]" value="1"' . checked( 1, $options['pigeon_content_pdf_paywall'], false ) . '/>';
		$html .= '<label for="pdf_paywall_enabled">' . esc_html__( 'Enabled', 'pigeon' ) . '</label><br />';
		$html .= '<input type="radio" id="pdf_paywall_disabled" class="pigeon-content-pdf-paywall" name="wp_pigeon_settings[pigeon_content_pdf_paywall]" value="0"' . checked( 0, $options['pigeon_content_pdf_paywall'], false ) . '/>';
		$html .= '<label for="pdf_paywall_disabled">' . esc_html__( 'Disabled', 'pigeon' ) . '</label>';
		$html .= '<p class="description">' . esc_html__( 'Hide PDF documents behind the paywall. All PDF documents uploaded to WordPress will be protected.', 'pigeon' ) . '</p>';

		if ( ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$server = strtolower( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) );
		}

		if ( empty( $server ) || false === strpos( $server, 'apache' ) ) {
			$html .= '<p class="description" style="color:#ca4a1f">' . esc_html__( 'Warning! It looks like you may not be running Apache. Nginx and other servers require a custom rewrite rule to be added for this to work. Please see the plugin readme file or contact Pigeon support for assistance.', 'pigeon' ) . '</p>';
		}

		echo $html; // @phpcs:ignore
	}

	/**
	 * Discourage indexing of PDFs.
	 *
	 * @since    1.6.0
	 */
	public function setting_pigeon_pdf_index() {
		$options = $this->get_settings();

		$options['pigeon_content_pdf_index'] = ! empty( $options['pigeon_content_pdf_index'] ) ? $options['pigeon_content_pdf_index'] : 0;

		$html  = '<input type="radio" id="pdf_index_enabled" class="pigeon-content-pdf-index" name="wp_pigeon_settings[pigeon_content_pdf_index]" value="1"' . checked( 1, $options['pigeon_content_pdf_index'], false ) . '/>';
		$html .= '<label for="pdf_index_enabled">' . esc_html__( 'Enabled', 'pigeon' ) . '</label><br />';
		$html .= '<input type="radio" id="pdf_index_disabled" class="pigeon-content-pdf-index" name="wp_pigeon_settings[pigeon_content_pdf_index]" value="0"' . checked( 0, $options['pigeon_content_pdf_index'], false ) . '/>';
		$html .= '<label for="pdf_index_disabled">' . esc_html__( 'Disabled', 'pigeon' ) . '</label>';
		$html .= '<p class="description">' . esc_html__( 'Encourage search engines like Google to exclude your uploaded PDF documents from their index.', 'pigeon' ) . '</p>';

		echo $html; // @phpcs:ignore
	}

	/**
	 * Save the sub domain to the database.
	 *
	 * @since 1.6.2
	 *
	 * @return void
	 */
	public function connect_pigeon() {
		if ( ! empty( $_POST['subdomain'] ) && ! empty( $_POST['nonce'] ) ) {
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pigeon-connect-nonce' ) ) {
				$subdomain = sanitize_text_field( wp_unslash( $_POST['subdomain'] ) );

				$settings                     = $this->get_settings();
				$settings['pigeon_subdomain'] = $subdomain;
				update_option( self::SETTINGS_KEY, $settings );
				wp_send_json_success( 'Connected.' );
			}
		}

		wp_die();
	}
}

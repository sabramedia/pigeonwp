<?php
/**
 * Metabox Template.
 *
 * @package PigeonWP
 */

namespace PigeonWP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$settings = get_plugin_settings();

$settings['pigeon_subdomain'] = str_replace( array( 'https://', 'http://' ), '', $settings['pigeon_subdomain'] );
?>
<div class="wrap">
	<h2><?php esc_html_e( 'Pigeon for WordPress', 'pigeon' ); ?></h2>
	<p>
		<?php esc_html_e( 'For questions regarding any of these settings please contact Pigeon support.', 'pigeon' ); ?>
		<?php if ( ! empty( $settings['pigeon_subdomain'] ) ) : ?>
			<a href="<?php echo esc_url( 'https://pigeon.io/api/sso?format=admin&return_to=https://' . $settings['pigeon_subdomain'] ); ?>/admin" target="_blank"><?php esc_html_e( 'Click here to access the Pigeon control panel', 'pigeon' ); ?></a>.
		<?php endif; ?>
	</p>

	<form action='options.php' method='post'>
		<?php
		settings_fields( 'plugin_options' );
		do_settings_sections( 'plugin_options' );

		$settings = get_plugin_settings();
		if ( ! empty( $settings['pigeon_subdomain'] ) || ! empty( $_GET['configure'] ) ) { // @phpcs:ignore
			submit_button();
		}
		?>
	</form>
</div>

<?php
/**
 * Metabox Template.
 *
 * @package PigeonWP
 */

namespace PigeonWP;

$settings = get_plugin_settings();

$settings['pigeon_subdomain'] = str_replace( array( 'https://', 'http://' ), '', $settings['pigeon_subdomain'] );
?>
<div class="wrap">
	<h2><?php esc_html_e( 'Pigeon for WordPress', 'pigeon' ); ?></h2>
	<p>
		<?php esc_html_e( 'For questions regarding any of these settings please contact Pigeon support.', 'pigeon' ); ?>
		<?php if ( ! empty( $settings['pigeon_subdomain'] ) ) : ?>
			<a href="<?php echo esc_url( 'https://' . $settings['pigeon_subdomain'] ); ?>/admin" target="_blank"><?php esc_html_e( 'Click here to access the Pigeon control panel', 'pigeon' ); ?></a>.
		<?php endif; ?>
	<p>
		<?php esc_html_e( 'Current Installed Version:', 'pigeon' ); ?> <?php echo esc_html( PIGEONWP_VERSION ); ?>
	</p>

	<form action='options.php' method='post'>
		<?php
		settings_fields( 'plugin_options' );
		do_settings_sections( 'plugin_options' );
		submit_button();
		?>
	</form>
</div>
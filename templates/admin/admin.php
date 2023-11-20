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
	<h2><?php esc_html_e( 'Pigeon for WordPress', 'pigeonwp' ); ?></h2>
	<p>
		<?php esc_html_e( 'For questions regarding any of these settings please contact Pigeon support.', 'pigeonwp' ); ?>
		<?php if ( ! empty( $settings['pigeon_subdomain'] ) ) : ?>
			<a href="<?php echo esc_url( 'https://' . $settings['pigeon_subdomain'] ); ?>/admin" target="_blank"><?php esc_html_e( 'Click here to access the Pigeon control panel', 'pigeonwp' ); ?></a>.
		<?php endif; ?>
	<p>
		<?php esc_html_e( 'Current Installed Version:', 'pigeonwp' ); ?> <?php echo esc_html( PIGEONWP_VERSION ); ?>
		(
			<?php
			printf(
				/* translators: Link to Github. */
				wp_kses_post( 'Download the latest Pigeon plugin for WordPress from <a href="%s">Github</a>', 'pigeonwp' ),
				esc_url( 'https://github.com/sabramedia/pigeonwp' )
			);
			?>
		)
	</p>

	<form action='options.php' method='post'>
		<?php
		settings_fields( 'plugin_options' );
		do_settings_sections( 'plugin_options' );
		submit_button();
		?>
	</form>
</div>
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
	<?php if ( ! empty( $settings['pigeon_subdomain'] ) || ! empty( $_GET['configure'] ) ) : // @phpcs:ignore ?>
		<style type="text/css">
			.pigeon-banner, .pigeon-banner *, .pigeon-banner *:before, .pigeon-banner *:after { box-sizing: border-box; margin: 0; padding: 0; }	  
			.pigeon-banner { max-width: 600px; margin: 25px 0; background: linear-gradient(135deg, #524dbf 0%, #0073c5 50%, rgba(0,173,203,1) 100%); color: #fff; border-radius: 5px; text-shadow: 0 1px 1px rgba(0,0,0,0.5); padding: 25px 180px 25px 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.15); position: relative; font-size: 16px; line-height: 1.5; font-weight: 600; }
			.pigeon-banner p:first-child { color: #1a1f36; color: #fff; font-size: 20px; line-height: 1.2; font-weight: 600; padding: 0; }
			.pigeon-banner p { color: #9beaff; padding-top: 5px; font-weight: 400; }
			.pigeon-banner .pigeon-button { display: inline-block; margin-top: 20px; background: #FDD948; background-image: linear-gradient(12deg, #FDCF1A -30%, #FDD948 130%); color: #1a1f36; border-radius: 5px; padding: 10px 15px; text-shadow: none; text-decoration: none; font-weight: 700; box-shadow: 0 2px 8px rgba(0,0,0,0.2), 0 1px 2px rgba(0,0,0,0.25); }
			.pigeon-banner .pigeon-button:hover { transform: scale(1.015); box-shadow: 0 3px 9px rgba(0,0,0,0.2), 0 1px 3px rgba(0,0,0,0.25); }
			.pigeon-banner:after { content: ""; position: absolute; bottom: 0; right: -50px; width: 250px; height: calc(100% + 15px); background: url(<?php echo esc_url_raw( get_plugin_url( 'images/pigeon-bird.png' ) ); ?>) no-repeat top left; background-size: 100% auto; transition: all 0.35s; }
			.pigeon-banner:hover:after { width: 250px; height: calc(100% + 25px); right: -50px; }
			.pigeon-visit-admin { max-width: 580px; }
			.pigeon-demo-admin {
				max-width: 710px; border-top: solid 30px;
				border-image: repeating-linear-gradient( -60deg, #FDCF1A, #FDD948 25px, #1e2227 25px, #353A40 48px) 60;
				background: linear-gradient(75deg, #254a7a 0%, #275b8d 50%, rgba(0,173,223,1) 100%);
			}
		</style>
		<h2><?php esc_html_e( 'Pigeon for WordPress', 'pigeon' ); ?></h2>

		<?php if ( empty( $settings['pigeon_demo'] ) ) : ?>
		<div class="pigeon-banner pigeon-visit-admin">
			<p><?php esc_html_e( 'To access the full selection of settings and features, visit your Pigeon admin.', 'pigeon' ); ?></p>
			<a class="pigeon-button" href="<?php echo esc_url( 'https://pigeon.io/api/sso?format=admin&return_to=https://' . $settings['pigeon_subdomain'] ); ?>/admin"><?php esc_html_e( 'Visit Pigeon Admin', 'pigeon' ); ?></a>
		</div>
		<?php else : ?>
		<div class="pigeon-banner pigeon-demo-admin">
			<p><?php esc_html_e( 'Pigeon is in Demo Mode', 'pigeon' ); ?></p>
			<p><?php esc_html_e( 'While in demo mode, the paywall is only visible to WordPress admins. Complete setup by visiting your Pigeon admin.', 'pigeon' ); ?></p>
			<a class="pigeon-button" href="<?php echo esc_url( 'https://pigeon.io/api/sso?format=admin&return_to=https://' . $settings['pigeon_subdomain'] ); ?>/admin"><?php esc_html_e( 'Visit Pigeon Admin', 'pigeon' ); ?></a>
		</div>
		<?php endif; ?>
	<?php endif; ?>

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

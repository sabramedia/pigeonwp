<?php
/**
 * Connection Template.
 *
 * @since 1.6.3
 *
 * @package PigeonWP
 */

namespace PigeonWP;

use function PigeonWP\get_plugin_url;
?>
<style type="text/css">
	.pigeon-page {
		display: flex;
		flex-direction: row;		  
		align-items: center;
		justify-content: center;
		min-height: 88vh;
		color: #6f7780;
		line-height: 1.4;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}

	.pigeon-page *,
	.pigeon-page *:before,
	.pigeon-page *:after { box-sizing: border-box; margin: 0; padding: 0; }
	.pigeon-page a { color: #2678bf; }

	.pigeon-box-container { max-width: 520px; }

	.pigeon-box {
		background: #fff;
		border-radius: 16px;
		box-shadow:  rgba(0, 0, 0, 0.05) 0px 4px 4px, rgba(0, 0, 0, 0.05) 0px 8px 8px, rgba(0, 0, 0, 0.05) 0px 16px 16px;
		text-align: center;
		overflow: hidden;
	}

	.pigeon-box .pigeon-box-header { background: url(<?php echo esc_url_raw( get_plugin_url( 'images/header-bg.jpg' ) ); ?>) no-repeat center top; background-size: auto 400px; padding: 30px 50px; border-bottom: 1px solid #e0e0e0; font-size: 15px; }
	.pigeon-box .pigeon-box-header img { display: block; width: 220px; margin: 5px auto 15px; }
	.pigeon-box h2 { font-size: 1.8rem; font-weight: 500; color: #353a40; }
	.pigeon-box .pigeon-box-body { padding: 35px 50px 40px 50px; font-size: 14px; }

	.pigeon-button { 
		display: block;
		background: linear-gradient(0deg, #2b408d -50%, #0289cb 105%);
		font-size: 18px;
		font-weight: 500;
		color: #fff !important;
		text-decoration: none;
		text-shadow: 0 1px 1px rgba(0,0,0,0.3);
		padding: 0.8em;
		margin-top: 1.5rem;
		border-radius: 6px;
		box-shadow: rgba(50, 50, 93, 0.2) 0px 5px 10px -2px, rgba(0, 0, 0, 0.25) 0px 3px 7px -3px, inset rgba(53, 188, 254, 0.8) 0px 1px 1px, inset rgba(37, 55, 121, 0.5) 0px -1px 1px;
	}
	.pigeon-manual-setup { display: block; margin: 8px 0 -18px 0; color: #949ba3 !important; font-size: 13px; font-weight: 300; }
	.pigeon-box-contact { text-align: center; font-size: 13px; font-style: italic; margin-top: 15px; opacity: 0.85; }
</style>

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

<div class="pigeon-page">
	<div class="pigeon-box-container">
		<div class="pigeon-box">
			<div class="pigeon-box-header">
				<img src="<?php echo esc_url_raw( get_plugin_url( 'images/connect.png' ) ); ?>">
				<h2><?php esc_html_e( 'Connect with Pigeon', 'pigeon' ); ?></h2>
				<p><?php esc_html_e( 'To continue setup, connect your site to Pigeon.', 'pigeon' ); ?></p>
			</div>
			<div class="pigeon-box-body">
				<p><?php esc_html_e( 'Adding Pigeon to your site is free and enables you to use Pigeon in Demo Mode. While in Demo Mode, you will be able to see how Pigeon works for yourself without affecting your visitors.', 'pigeon' ); ?></p>
				<a class="pigeon-button" href="#" onclick="window.open( 'https://pigeon.io/cmc/register?src=wp&origin=<?php echo esc_url_raw( get_site_url() ); ?>', '_blank', 'location=yes,height=720,width=720' );"><?php esc_html_e( 'Connect to Pigeon', 'pigeon' ); ?></a>
				<a class="pigeon-manual-setup" href="<?php echo esc_url( admin_url( 'options-general.php?page=pigeon&configure=1' ) ); ?>"><?php esc_html_e( 'Configure manually instead?', 'pigeon' ); ?></a>
			</div>
		</div>
		<div class="pigeon-box-contact">
			<?php
			printf(
				'If you have any questions regarding Pigeon, <a href="%1$s">contact us or schedule a call</a>.',
				'https://calendly.com/pigeonpaywall/onboarding'
			);
			?>
		</div>
	</div>
</div>
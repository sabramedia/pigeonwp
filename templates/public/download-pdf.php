<?php
/**
 * Download PDF template.
 *
 * @package PigeonWP
 */

namespace PigeonWP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<html lang="en-US">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><?php esc_html_e( 'Downloading PDF...', 'pigeon' ); ?></title>
		<?php wp_head(); ?>
		<script>
			Pigeon.paywallPromise.done(function(response){
				if ( response.allowed ){
					window.location.replace(window.location.href + '?download_key=<?php echo esc_attr( wp_create_nonce( PDF::NONCE_ACTION ) ); ?>');
				}
			});
		</script>
	</head>
	<body>
		<?php esc_html_e( 'Checking PDF download permissions, please wait...', 'pigeon' ); ?>
		<?php wp_footer(); ?>
	</body>
</html>
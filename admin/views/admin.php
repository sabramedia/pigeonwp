<?php
$admin_options = get_option( 'wp_pigeon_settings' );
$admin_options["pigeon_subdomain"] = str_replace(array("https://","http://"),"",$admin_options["pigeon_subdomain"]);
?>
<div class="wrap">

	<h2>Pigeon for WordPress</h2>
	
	<p>For questions regarding any of these settings please contact Pigeon support. <?php echo $admin_options["pigeon_subdomain"] ? "<a href=\"http://".$admin_options["pigeon_subdomain"]."/admin\" target=\"_blank\">Click here to access the Pigeon control panel</a>." : ""; ?> </p>

	<form action='options.php' method='post'>
			
		<?php
		settings_fields( 'plugin_options' );
		do_settings_sections( 'plugin_options' );
		submit_button();
		?>
		
	</form>

</div>
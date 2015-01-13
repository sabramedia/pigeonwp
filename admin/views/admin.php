
<div class="wrap">

	<h2>Pigeon for WordPress</h2>
	
	<p>For questions regarding any of these settings please contact Pigeon support.</p>

	<form action='options.php' method='post'>
			
		<?php
		settings_fields( 'plugin_options' );
		do_settings_sections( 'plugin_options' );
		submit_button();
		?>
		
	</form>

</div>
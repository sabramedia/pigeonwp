<table>
	<tr>
		<td>
			<label for="pigeon-content-access">Access</label>
		</td>
		<td>
			<select name="pigeon_content_access" id="pigeon-content-access">
				<option value="0"<?php if ( $access_value == 0 ) echo ' selected'; ?>>Metered</option>
				<option value="1"<?php if ( $access_value == 1 ) echo ' selected'; ?>>Public</option>
				<option value="2"<?php if ( $access_value == 2 ) echo ' selected'; ?>>Restricted</option>
			</select>
		</td>
	</tr>

<?php if( isset($options['pigeon_content_value_meter']) && $options['pigeon_content_value_meter'] == 1 && isset($options['pigeon_content_value']) ){ ?>
	<tr>
		<td>
			<label for="pigeon-content-value">Value</label>
		</td>
		<td>
			<select name="pigeon_content_value" id="pigeon-content-value">
				<option value="0">-- Free --</option>
			<?php
				foreach( $options['pigeon_content_value'] as $option ){
			?>

				<option value="<?php echo $option; ?>"<?php if ( $option == $content_value ) echo ' selected'; ?>><?php echo $option." credit".( $option > 1 ? "s" : "" ); ?></option>
			<?php
				}
			?>
			</select>
			<span style="padding-left:5px;">Prompt <input type="checkbox" name="pigeon_content_prompt" value="1" <?php if ( $content_prompt == 1 ) echo ' checked'; ?>  /></span>
			<?php } ?>
		</td>
	</tr>
</table>
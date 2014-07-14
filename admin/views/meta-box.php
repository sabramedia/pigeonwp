<label for="pigeon-content-access"></label>
<select name="pigeon_content_access" id="pigeon-content-access">
	<option value="0"<?php if ( $value == 0 ) echo ' selected'; ?>>Metered</option>
	<option value="1"<?php if ( $value == 1 ) echo ' selected'; ?>>Public</option>
	<option value="2"<?php if ( $value == 2 ) echo ' selected'; ?>>Restricted</option>
</select>
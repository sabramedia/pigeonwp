<?php
/**
 * Metabox Template.
 *
 * @package PigeonWP
 */

namespace PigeonWP;

$access_value   = get_post_meta( $post->ID, '_wp_pigeon_content_access', true );
$content_price  = get_post_meta( $post->ID, '_wp_pigeon_content_price', true );
$content_value  = get_post_meta( $post->ID, '_wp_pigeon_content_value', true );
$content_prompt = get_post_meta( $post->ID, '_wp_pigeon_content_prompt', true );

$settings = get_plugin_settings();

?>
<table>
	<tr>
		<td>
			<label for="pigeon-content-access"><?php esc_html_e( 'Access', 'pigeonwp' ); ?></label>
		</td>
		<td>
			<select name="pigeon_content_access" id="pigeon-content-access">
				<option value="0" <?php selected( (int) $access_value, 0 ); ?>><?php esc_html_e( 'Metered', 'pigeonwp' ); ?></option>
				<option value="1" <?php selected( (int) $access_value, 1 ); ?>><?php esc_html_e( 'Public', 'pigeonwp' ); ?></option>
				<option value="2" <?php selected( (int) $access_value, 2 ); ?>><?php esc_html_e( 'Restricted', 'pigeonwp' ); ?></option>
			</select>
		</td>
	</tr>

	<?php if ( ! empty( $settings['pigeon_content_value_pricing'] ) && 1 === (int) $settings['pigeon_content_value_pricing'] ) { ?>
	<tr>
		<td>
			<label for="pigeon-content-price"><?php esc_html_e( 'Price', 'pigeonwp' ); ?>
				<div style="font-size:xx-small; margin-top: -5px;"><em>(e.g., 4.95)</em></div>
			</label>
		</td>
		<td>
			<input type="text" name="pigeon_content_price" value="<?php echo esc_attr( $content_price ); ?>" id="pigeon-content-price" style="width:95px;" />
		</td>
	</tr>
	<?php } ?>

	<?php if ( ! empty( $settings['pigeon_content_value_meter'] ) && 1 === (int) $settings['pigeon_content_value_meter'] && isset( $settings['pigeon_content_value'] ) ) : ?>
	<tr>
		<td>
			<label for="pigeon-content-value"><?php esc_html_e( 'Value', 'pigeonwp' ); ?></label>
		</td>
		<td>
			<select name="pigeon_content_value" id="pigeon-content-value">
				<option value="0">-- <?php esc_html_e( 'Free', 'pigeonwp' ); ?> --</option>
				<?php foreach ( $settings['pigeon_content_value'] as $value ) : ?>
					<?php // translators: Credits value. ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (int) $content_value, (int) $value ); ?>><?php echo esc_html( _n( '%s credit', '%s credits', $value, 'pigeonwp' ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<span style="padding-left:5px;"><?php esc_html_e( 'Prompt', 'pigeonwp' ); ?> <input type="checkbox" name="pigeon_content_prompt" value="1" <?php checked( (int) $content_prompt, 1 ); ?>></span>
		</td>
	</tr>
	<?php endif; ?>
</table>

<?php wp_nonce_field( Admin::NONCE_ACTION, Admin::NONCE_NAME ); ?>

<?php

if ( ! function_exists( 'get_pigeon_value' ) ) {

	/**
	 * Get Pigeon Setting
	 *
	 * @since     1.0.0
	 */
	function get_pigeon_value( $key ) {
		
		$pigeon_obj = WP_Pigeon::get_instance();

		if ( is_object( $pigeon_obj ) ) {
			$pigeon_values = $pigeon_obj->get_pigeon_values();

			if ( isset( $pigeon_obj->pigeon_values[$key] ) ) {
				return $pigeon_obj->pigeon_values[$key];
			}
		}

		return false;

	}

	/**
	 * Set Pigeon Option
	 *
	 * @since     1.0.0
	 */
	function set_pigeon_option( $key, $value ) {

		// We currently only allow the redirect option to be set
		if ( ! in_array( $key, array( 'redirect' ) ) )
			return;

		$pigeon_obj = WP_Pigeon::get_instance();
		$pigeon_obj->pigeon_settings[$key] = wp_kses( $value, array() );

	}

}
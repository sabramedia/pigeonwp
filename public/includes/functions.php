<?php

if ( ! function_exists( 'get_pigeon_setting' ) ) {

	/**
	 * Get Pigeon Setting
	 *
	 * @since     1.0.0
	 */
	function get_pigeon_setting( $key ) {
		
		$pigeon_obj = WP_Pigeon::get_instance();

		if ( is_object( $pigeon_obj ) ) {
			$pigeon_settings = $pigeon_obj->get_pigeon_settings();

			if ( isset( $pigeon_obj->pigeon_settings[$key] ) ) {
				return $pigeon_obj->pigeon_settings[$key];
			}
		}

		return false;

	}

}
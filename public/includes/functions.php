<?php

if ( ! function_exists( 'get_pidgeon_setting' ) ) {

	/**
	 * Get Pidgeon Setting
	 *
	 * @since     1.0.0
	 */
	function get_pidgeon_setting( $key ) {
		
		$pidgeon_obj = WP_Pidgeon::get_instance();

		if ( is_object( $pidgeon_obj ) ) {
			$pidgeon_settings = $pidgeon_obj->get_pidgeon_settings();

			if ( isset( $pidgeon_obj->pidgeon_settings[$key] ) ) {
				return $pidgeon_obj->pidgeon_settings[$key];
			}
		}

		return false;

	}

}
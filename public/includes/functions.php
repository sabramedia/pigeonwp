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
}
if ( ! function_exists( 'get_pigeon_post_meta' ) ) {

	/**
	 * Get Pigeon metadata in a post loop
	 *
	 * @since     1.4.4
	 */
	function get_pigeon_post_meta( $post_id = NULL ) {
		if( ! $post_id ){
			global $post;
			$post_id = $post->ID;
		}

		// Set defaults
		$pigeon_values = array(
			"content_value"=>0,
			"content_access"=>1,
			"content_prompt"=>0
		);
		foreach( get_post_meta($post_id) as $key=>$pm ){
			if(strpos($key,"_wp_pigeon_") !== FALSE ){
				$pigeon_values[str_replace("_wp_pigeon_","",$key)] = $pm[0];
			}
		}

		return $pigeon_values;

	}
}
if ( ! function_exists( 'set_pigeon_access' ) ) {

	/**
	 * Get Pigeon Setting
	 *
	 * @since     1.3.1
	 */
	function set_pigeon_access( $level ) {

		$pigeon_obj = WP_Pigeon::get_instance();

		if ( is_object( $pigeon_obj ) ) {
			switch( strtolower($level) ){
				case "metered": $pigeon_obj->pigeon_content_access = 0; break; // Respects meter rules whether logged in or not
				case "public": $pigeon_obj->pigeon_content_access = 1; break; // No restrictions
				case "restricted": $pigeon_obj->pigeon_content_access = 2; break; // You have to be logged in with access permissions
			}
		}

		return false;
	}
}
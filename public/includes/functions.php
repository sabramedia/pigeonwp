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

	function get_pigeon_values( ) {

		$pigeon_obj = WP_Pigeon::get_instance();

		if ( is_object( $pigeon_obj ) ) {
			return $pigeon_values = $pigeon_obj->get_pigeon_values();
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
			"content_price"=>0,
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


/**
 * Update RSS feeds with Pigeon Access meta data
 *
 * @since     1.5.8
 */
if ( ! function_exists( 'parse_pigeon_access_rss' ) ) {

	function parse_pigeon_access_rss()
	{

		var_dump(function_exists("curl_init"));
		$url_array = [];
		global $wp_query;

		foreach($wp_query->get_posts() as $post ){
			$url_array[$post->ID] = get_permalink($post->ID);
		}

		print_r($url_array);
		$admin_options = get_option( 'wp_pigeon_settings' );
		$pigeon_subdomain = $admin_options["pigeon_subdomain"] ? str_replace(array("https://","http://"),"",$admin_options["pigeon_subdomain"]): 'my.' . str_replace( 'www.', '', $_SERVER["HTTP_HOST"] );
		$ch = curl_init();

		curl_setopt_array(
			$ch,
			array(
				CURLOPT_URL => $pigeon_subdomain.'/action/public/vo/pigeon-server',
				CURLOPT_TIMEOUT => 15,
				CURLOPT_VERBOSE => 1,
				CURLOPT_COOKIE => 1,
				CURLOPT_SSL_VERIFYPEER => FALSE,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_POST => 1,
				CURLOPT_USERAGENT => ( array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) ? $_SERVER['HTTP_USER_AGENT'] : '' ),
				CURLOPT_POSTFIELDS => 'action=check_urls&json=' . json_encode( $url_array )
			)
		);

		print_r(curl_exec( $ch ));
		$response = json_decode(curl_exec( $ch ),TRUE);
		echo "\t<pigeonServer>\n";
		foreach( $url_array as $key=>$url ){
			$access = (int)$response[$key];
			echo "\t\t<item id=\"{$key}\" access=\"{$access}\">{$url}</item>\n";
		}
		echo "\t</pigeonServer>\n";

		return ;
	}

	add_action('rss2_head', 'parse_pigeon_access_rss');

	if ( ! function_exists( 'add_pigeon_field_to_rss' ) ) {

		function add_pigeon_field_to_rss()
		{
			global $post,$response;

			$pigeon_meta_values = get_pigeon_post_meta($post->ID);
			if (array_key_exists("content_access",$pigeon_meta_values) ) {
				$pigeon_access = $pigeon_meta_values["content_access"];
			}else{
				// If the content_access is not set locally then grab the Pigeon Server version
				$pigeon_access = isset($response[$post->ID]) ? $response[$post->ID] : 1; // Default to public if not set
			}

			echo "\n\t\t<pigeonAccess>{$pigeon_access}</pigeonAccess>\n";
		}

		add_action('rss2_item', 'add_pigeon_field_to_rss');
		add_action('rss_item', 'add_pigeon_field_to_rss');
	}
}
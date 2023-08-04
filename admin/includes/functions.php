<?php

/**
 * Sync post category data with Pigeon servers so registered users can pick category preferences.
 *
 * @since     1.5.9
 */
function pigeon_category_save()
{
	// Get categories
	$args = array(
		"orderby"   => "name",
		"order"     => "ASC",
		"hide_empty"    => "0",
	);

//	echo "pigeon_category_save";
//	exit;

	$categories = get_categories($args);


    // Load the API class
	require_once( plugin_dir_path( __FILE__ ). "../../sdk/Pigeon.php");
	$admin_options = get_option( 'wp_pigeon_settings' );
	Pigeon_Configuration::clientId($admin_options['pigeon_api_user']);
	Pigeon_Configuration::apiKey($admin_options['pigeon_api_secret_key']);
	Pigeon_Configuration::pigeonDomain($admin_options['pigeon_subdomain']);

	// Send the category array
    $pigeon_sdk = new Pigeon();
    $pigeon_sdk->post("/plugin/wp_preferences/sync_category", ["categories"=>$categories]);
}

// Only add the hooks if the category preferences is enabled.
$options = get_option( "wp_pigeon_settings" );
if( isset($options["pigeon_content_pref_category"]) && $options["pigeon_content_pref_category"] == 1 ) {
	add_action("created_term", "pigeon_category_save");
	add_action("edited_term", "pigeon_category_save");
	add_action("delete_category", "pigeon_category_save");
}

function pigeon_category_enable()
{
	// Load the API class
	$admin_options = get_option( 'wp_pigeon_settings' );
	if( $admin_options['pigeon_api_user'] && $admin_options['pigeon_api_secret_key'] ) {
		try {
			require_once(plugin_dir_path(__FILE__) . "../../sdk/Pigeon.php");
			Pigeon_Configuration::clientId($admin_options['pigeon_api_user']);
			Pigeon_Configuration::apiKey($admin_options['pigeon_api_secret_key']);
			Pigeon_Configuration::pigeonDomain($admin_options['pigeon_subdomain']);

			// Send the category array
			$pigeon_sdk = new Pigeon();
			$pigeon_sdk->post("/plugin/wp_preferences/enable_category_plugin", []);
			return TRUE;
		}catch( Exception $e ){
			return FALSE;
		}
	}else{
		return FALSE;
	}
}

function pigeon_category_disable()
{
	$admin_options = get_option('wp_pigeon_settings');
	if( $admin_options['pigeon_api_user'] && $admin_options['pigeon_api_secret_key'] ) {
		try {
			// Load the API class
			require_once(plugin_dir_path(__FILE__) . "../../sdk/Pigeon.php");
			Pigeon_Configuration::clientId($admin_options['pigeon_api_user']);
			Pigeon_Configuration::apiKey($admin_options['pigeon_api_secret_key']);
			Pigeon_Configuration::pigeonDomain($admin_options['pigeon_subdomain']);

			// Send the category array
			$pigeon_sdk = new Pigeon();
			$pigeon_sdk->post("/plugin/wp_preferences/disable_category_plugin", []);
			return TRUE;
		}catch( Exception $e ){
			return FALSE;
		}
	}else{
		return FALSE;
	}
}
<?php

class Pigeon_Cookie implements StaticDictionary
{
	
	const SSL_ENABLED = TRUE;
	
	/*
	//DEFAULTS
	$configure_array = array(
		"timeout" => 0,
		"path" => "/",
		"domain" => "www.site.com",
		"secure" => FALSE,
		"httponly" => FALSE
	)
	*/
	static public function set( $key, $value, array $configure_array = array() )
	{
		if( ! is_string( $value ) ) throw new Exception( "You can only store strings in cookies" );
		$configure_array = array_merge(
			array(
				"timeout" => 0,
				"path" => "/",
				"domain" => NULL,
				"secure" => FALSE,
				"httponly" => FALSE
			),
			$configure_array
		);

		if( is_null( $configure_array["domain"] ) )
			$configure_array["domain"] = ".".$_SERVER["HTTP_HOST"];

		if( self::SSL_ENABLED ) {
//			if( $configure_array["secure"] && Common::checkSSL() )
//				throw new Exception( "Can't send secure cookie over non-secure connection" );
		} else {
			$configure_array["secure"] = FALSE;		
		}

		if( ! setcookie( $key, $value, $configure_array["timeout"], $configure_array["path"], $configure_array["domain"], $configure_array["secure"], $configure_array["httponly"] ) )
			throw new Exception( "Cookie can't be put in header because it's already sent" );
	}

	static public function get( $key )
	{
		if( ! array_key_exists( $key, $_COOKIE ) ) throw new Exception( "Bad key access w/o checking it first: ".$key );
		return $_COOKIE[$key];
	}

	static public function remove( $key )
	{
		setcookie( $key, FALSE );
	}

	static public function check( $key )
	{
		return array_key_exists( $key, $_COOKIE );
	}

}

?>
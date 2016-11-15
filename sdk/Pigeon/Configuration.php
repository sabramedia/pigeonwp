<?php

class Pigeon_Configuration implements StaticDictionary
{
	static $config_data = NULL;

	static private function getInstance()
	{
		static $instance = NULL;
		if( is_null( $instance ) )
			$instance = new self();
		
		return $instance;
	}
	
	private function __construct()
	{
		self::getData();
	}

	static function getData()
	{
		if( is_null( self::$config_data ) ) {
			// Eventually pull the config data from database
			self::$config_data = array();
		}
		return self::$config_data;
	}

	static function check( $key )
	{
		return array_key_exists( $key, self::getData() );
	}
	
	static function set( $key, $value )
	{
		self::getInstance();
		return self::$config_data[$key] = $value;
	}
	
	static function get( $key )
	{
		$config_data = self::getData();
		if( ! array_key_exists( $key, self::$config_data ) ) throw new Exception( "bad config key accessed without checking if it's set first: {$key}" );
		
		return $config_data[$key];
	}
	
	static function remove( $key )
	{
		$config_data = self::getData();
		if( array_key_exists( $key, $config_data ) ) { //if it exists in the local cache, remove it
			unset( $config_data[$key] );
		}
	}

	// Set specific setters and gets here.
	static public function pigeonDomain( $domain )
	{

		if( ! self::check("pigeon_domain") ){
			self::set("pigeon_domain", $domain);
		}
	}
	static public function clientId( $id )
	{
		if( ! self::check("client_id") ){
			self::set("client_id", $id);
		}
	}

	static public function apiKey( $key )
	{
		if( ! self::check("api_key") ){
			self::set("api_key", $key);
		}
	}

	static public function getClientId()
	{
		if( self::check("client_id") ){
			return self::get('client_id');
		}else{
			return FALSE;
		}
	}

	static public function getApiKey()
	{
		if( self::check("api_key") ){
			return self::get('api_key');
		}else{
			return FALSE;
		}
	}
}
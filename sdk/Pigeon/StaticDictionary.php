<?php

interface StaticDictionary
{

	/*
		set:
			-stores $value by $key
			-must work if $key exists or not
		get:
			-returns $value in $key
			-must throw error if not existing
		remove:
			-remove $value stored in $key
			-should work even if $key isn't set
		check:
			-returns if $key is set
	*/

	static public function set( $key, $value );
	static public function get( $key );
	static public function remove( $key );
	static public function check( $key );

}

?>
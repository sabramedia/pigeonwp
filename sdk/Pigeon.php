<?php

set_time_limit("5");
/**
 * Pigeon PHP Library
 *
 * Pigeon base class and initialization
 *
 *  PHP version 5
 *
 * @copyright  2016 Pigeon Paywall, a division of Sabramedia, LLC.
 */


set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)));

require_once('Pigeon/StaticDictionary.php');
require_once('Pigeon/Configuration.php');
require_once('Pigeon/Cookie.php');
require_once('Pigeon/Curl.php');
require_once('Pigeon/CurlResponse.php');
require_once('Pigeon/Customer.php');
require_once('Pigeon/Plan.php');


function requireDependencies() {
    $requiredExtensions = array('curl');
    foreach ($requiredExtensions AS $ext) {
        if (!extension_loaded($ext)) {
            throw new Exception('The Pigeon library requires the ' . $ext . ' extension.');
        }
    }
}

requireDependencies();

class Pigeon
{
	private $_conf;

	public function __construct()
	{
		Pigeon_Configuration::set("major_version","1");
		Pigeon_Configuration::set("user_agent","Pigeon-SDK-PHP");
		$this->_conf = Pigeon_Configuration::getData();
	}

	public function __get( $key )
	{
		$class_name = "Pigeon_".$key;
		if( !class_exists($class_name) )
			throw new Exception("Noun not setup yet");

		return new $class_name();
	}

	public function get( $api_path, $params )
	{
		$response = $this->_doRequest( "GET", $api_path, $params);
		if( $response["status"] === 200 ){
			return $response["body"];
		}else{
			throw new Exception("Error ".$response["status"]);
		}
	}

	public function post( $api_path, $params )
	{
		$response = $this->_doRequest( "POST", $api_path, $params);
		if( $response["status"] === 200 || $response["status"] === 201 || $response["status"] === 422 ){
			return $response["body"];
		}else{
			throw new Exception("Error ".$response["status"]);
		}
	}

	public function put( $api_path, $params )
	{
		$response = $this->_doRequest( "Put", $api_path, $params);
		if( $response["status"] === 200 || $response["status"] === 201 || $response["status"] === 422 ){
			return $response["body"];
		}else{
			throw new Exception("Error ".$response["status"]);
		}
	}

	private function _doRequest( $http_verb, $api_path, $params )
	{
		$curl = new Curl();
		$curl->cookie_file = FALSE;
		$curl->user_agent = $this->_conf["user_agent"] . " " . $this->_conf["major_version"];
		$curl->headers = array();
		$curl->headers["X-Pigeon-API-Version"] = $this->_conf["major_version"];

		$params = array_merge($params, array(
			"client_id"=>$this->_conf["client_id"],
			"api_key"=>$this->_conf["api_key"]
		));

		$prepared = array();

		switch( $http_verb ){
			case "DELETE":
				// TODO fill in
				break;

			case "GET":
				$prepared = $params;
				$curl_method = "get";
				break;

			case "POST":
				$json_encoded = json_encode($params);
				$curl->headers = array_merge($curl->headers, array(
					"Content-Type"=> "application/json",
					"Content-Length" => strlen($json_encoded)
				));

				$prepared = $json_encoded;
				$curl_method = "post";
				break;

			case "PUT":
				// TODO Distinguish
				break;
		}

		if( ! Pigeon_Configuration::check("pigeon_domain") )
			throw new Exception("The Pigeon path needs to be set.");



		$response = $curl->{$curl_method}("https://" . Pigeon_Configuration::get("pigeon_domain") . "/api". $api_path, $prepared);

//		print_r($response->headers);
//		print_r($curl->getInfo());
		return array("status"=>(int)$response->headers["Status-Code"], "body"=>json_decode( $response->body ));
	}
}

?>
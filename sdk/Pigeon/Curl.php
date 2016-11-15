<?php
/**
 * Curl, CurlResponse
 *
 * Author Sean Huber - shuber@huberry.com
 * Date May 2008
 *
 * Customizations made by Sabramedia, LLC
 * Date 2013 - 2016
 *
 * A basic CURL wrapper for PHP
 * See the README for documentation/examples or http://php.net/curl for more information about the libcurl extension for PHP
 */

class Curl 
{
 
	public $cookie_file = 'curl_cookie.txt';
	public $headers = array();
	public $options = array();
	public $referer = '';
	public $user_agent = '';
	public $info = "";

	protected $error = '';

	public function __construct() {
		$this->user_agent = array_key_exists("HTTP_USER_AGENT", $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : "";
	}

	public function delete($url, $vars = array()) {
		return $this->request('DELETE', $url, $vars);
	}

	public function error() {
		return $this->error;
	}

	public function get($url, $vars = array()) {
		if (!empty($vars)) {
		  $url .= (stripos($url, '?') !== false) ? '&' : '?';
		  $url .= http_build_query($vars);
		}
		return $this->request('GET', $url);
	}

	public function post($url, $vars = array()) {
		return $this->request('POST', $url, $vars);
	}

	public function put($url, $vars = array()) {
		return $this->request('PUT', $url, $vars);
	}

	protected function request($method, $url, $vars = array()) {
		$handle = curl_init();

		# Determine the request method and set the correct CURL option
		switch ($method) {
		  case 'GET':
			curl_setopt($handle, CURLOPT_HTTPGET, true);
			break;
		  case 'POST':
			curl_setopt($handle, CURLOPT_POST, true);
			if(is_array($vars)){
				$vars = http_build_query($vars);
			}

			curl_setopt($handle, CURLOPT_POSTFIELDS, $vars);
			break;
		  default:
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
			break;
		}

		# Set some default CURL options
		curl_setopt($handle, CURLOPT_COOKIEFILE, $this->cookie_file);
		curl_setopt($handle, CURLOPT_COOKIEJAR, $this->cookie_file);
		curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($handle, CURLOPT_HEADER, true);
		curl_setopt($handle, CURLOPT_REFERER, $this->referer);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_USERAGENT, $this->user_agent);
		//curl_setopt($handle, CURLINFO_HEADER_OUT, TRUE);

		# Format custom headers for this request and set CURL option
		$headers = array();
		foreach ($this->headers as $key => $value) {
		  $headers[] = $key.': '.$value;
		}
		curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);



		# Set any custom CURL options
		foreach ($this->options as $option => $value) {
			curl_setopt($handle, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
		}

		$response = curl_exec($handle);

		if ($response) {
			$response = new CurlResponse($response);
		} else {
			$this->error = curl_errno($handle).' - '.curl_error($handle);
		}
		$this->info = curl_getinfo($handle);
		curl_close($handle);
		return $response;
	}

	public function getInfo()
	{
		return $this->info;
	}
}
?>
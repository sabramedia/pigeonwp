<?php

class Pigeon_Customer extends Pigeon
{

	public $customer_id = NULL;

	public function isLoaded()
	{
		if( $this->customer_id ){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	public function create( $input, $force_auth = FALSE )
	{
		if( !isset($input["customer_id"]) && !array_key_exists("email", $input) ){
			throw new Exception("An email is required for account creation");
		}

		$response = $this->post("/customer", array("data"=>$input,"force_auth"=>$force_auth));
		if( isset($response->customer->id) )
			$this->customer_id = $response->customer->id;

		return $response;
	}

	public function update( $customer_id, $input )
	{
		if( !$customer_id )
			throw new Exception("Update needs a customer id");

		$input["customer_id"] = $customer_id;
		$input["send_notice"] = FALSE;

		$this->create($input);
	}

	/**
	 * Get the current customer by session data
	 */
	public function getLoggedIn()
	{
		$response = $this->isSessionLoggedIn();
		return $response ? $response->customer : FALSE;
	}

	public function find( $customer_id )
	{
		$response = parent::get("/customer", array("id"=>$customer_id));

		if( isset($response->customer->id) )
			$this->customer_id = $response->customer->id;

		return $response;
	}

	public function search( $filters )
	{
		if( !is_array($filters) )
			$filters = array("search"=>$filters);

		return parent::get("/customer/search", $filters);
	}

	/**
	 * @param $email
	 * @param $password
	 * @return mixed
	 *
	 * Add for semantics
	 */
	public function auth( $email, $password )
	{
		return $this->login($email, $password);
	}

	public function login( $email, $password )
	{
		$response = $this->post("/customer/login", array("email"=>$email,"password"=>$password));
		if( isset($response->customer->id) ){
			self::setLocalSession($response->session->id, $response->session->hash, $response->session->timeout);
		}

		return $response;
	}

	public static function setLocalSession( $session_id, $session_hash, $session_timeout )
	{
		$unique_session = md5(Pigeon_Configuration::get("pigeon_domain"));

		// Sets cookie for customer session handling
		Pigeon_Cookie::set( $unique_session."_id", (string)$session_id, array(
			"timeout" => $session_timeout,
			"domain" => ".".$_SERVER["HTTP_HOST"]
		) );
		Pigeon_Cookie::set( $unique_session."_hash", $session_hash, array(
			"timeout" => $session_timeout,
			"domain" => ".".$_SERVER["HTTP_HOST"]
		) );
	}

	public function sessionLogin( $customer_id )
	{
		$unique_session = md5(Pigeon_Configuration::get("pigeon_domain"));
		if( array_key_exists($unique_session."_id", $_COOKIE) && array_key_exists($unique_session."_hash", $_COOKIE) ){
			$response = $this->post("/customer/session_login", array("customer_id"=>$customer_id,"session_id"=>$_COOKIE[$unique_session."_id"],"session_hash"=>$_COOKIE[$unique_session."_hash"]));
			if( isset($response->customer->id) )
				$this->customer_id = $response->customer->id;

			return $response;
		}else{
			return FALSE;
		}
	}

	public function sessionLogout( $customer_id = "" )
	{
		$unique_session = md5(Pigeon_Configuration::get("pigeon_domain"));
		if( array_key_exists($unique_session."_id", $_COOKIE) && array_key_exists($unique_session."_hash", $_COOKIE) ){
			$this->customer_id = NULL;
			if( !$customer_id ){
				$response = $this->get("/customer/is_logged_in", array("session_id"=>$_COOKIE[$unique_session."_id"],"session_hash"=>$_COOKIE[$unique_session."_hash"]));
				$customer_id = $response->customer->id;
			}
			return $this->post("/customer/session_logout", array("customer_id"=>$customer_id,"session_id"=>$_COOKIE[$unique_session."_id"],"session_hash"=>$_COOKIE[$unique_session."_hash"]));
		}else{
			return FALSE;
		}
	}

	public function isSessionLoggedIn()
	{
		$unique_session = md5(Pigeon_Configuration::get("pigeon_domain"));
		if( array_key_exists($unique_session."_id", $_COOKIE) && array_key_exists($unique_session."_hash", $_COOKIE) ){
			$response = $this->get("/customer/is_logged_in", array("session_id"=>$_COOKIE[$unique_session."_id"],"session_hash"=>$_COOKIE[$unique_session."_hash"]));
			if( isset($response->customer->id) ){
				return $response;
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}

	/**
	 * @param $id_or_token
	 * @param string $type
	 * @return mixed
	 *
	 * default is sending token, but the user id can be used, but will remove all
	 * user sessions created via api, which in most cases will only be one.
	 */
	public function logout( $id_or_token, $type = "token" )
	{
		$this->customer_id = NULL;
		return $this->post("/customer/logout", array("token"=>$id_or_token,"type"=>$type));
	}

	public function getSSOLink( $customer_id, $url )
	{
		$sso_encoded = base64_encode(http_build_query(array("customer_id"=>$customer_id,"rd"=>$url)));
		$signature = hash_hmac("sha256",$sso_encoded,Pigeon_Configuration::get("api_key"));
		return (isset($_SERVER["HTTPS"]) ? "https" : "http")."://" . Pigeon_Configuration::get("pigeon_domain")."?psso=".$sso_encoded."&sig=".$signature;
	}

	public function SSOLogin( $customer_id, $url="" )
	{
		header("Location: " . $this->getSSOLink($customer_id, ($url ? $url : ($_SERVER["HTTPS"] ? "https" : "http")."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"])));
	}

	public function SSOLogout( $url="" )
	{
		$this->sessionLogout();
		header("Location: " .  ($url ? $url : isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "/" ));
	}

	private function _setSSOSession( $session_response )
	{
		self::setLocalSession($session_response["id"],$session_response["hash"],$session_response["timeout"]);
	}

	public function isSSOLoggedIn()
	{
		$customer = $this->isSessionLoggedIn();
		if( $customer ){
			return $customer;
		}else{
			if( array_key_exists("psso_hash", $_GET ) ){
				// Auth the signature - The api secret is the glue
				$authorized = $_GET["sig"] == hash_hmac("sha256", $_GET["psso_hash"], Pigeon_Configuration::get("api_key"));
				if( $authorized ){

					parse_str(base64_decode($_GET["psso_hash"]),$psso);
					if( $psso["customer_id"] ){
						$this->_setSSOSession( $psso["session"] );
						$actual_link = (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}";
						$parsed = parse_url($actual_link);
						parse_str($parsed["query"],$q_arr);
						unset($q_arr["psso_hash"],$q_arr["sig"]);
						header( "Location: ".$parsed["scheme"]."://".$parsed["host"].(isset($parsed["path"]) ? $parsed["path"] : "").(isset($qarr) ? http_build_query($qarr) : "") );
					}else{
						// Redirect to login page
						if( Pigeon_Configuration::check("sso_auth_failed_redirect") ){
							if( Pigeon_Configuration::get("sso_auth_failed_redirect") === TRUE ) {
								header("Location: " . Pigeon_Configuration::get("sso_auth_failed_redirect"));
							}elseif( Pigeon_Configuration::get("sso_auth_failed_redirect") === FALSE){
								return FALSE;
							}else{
								header( "Location: ".(isset($_SERVER["HTTPS"]) ? "https" : "http")."://" . Pigeon_Configuration::get("pigeon_domain") );
							}
						}else{
							return FALSE;
						}
					}
				}
			}else{
				$sso_encoded = base64_encode(http_build_query(array("rd"=>Pigeon_Configuration::check("sso_auth_url") ? Pigeon_Configuration::get("sso_auth_url") : (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}" )));
				$signature = hash_hmac("sha256",$sso_encoded,Pigeon_Configuration::get("api_key"));
				//build header string
				header( "Location: ".(isset($_SERVER["HTTPS"]) ? "https" : "http")."://" . Pigeon_Configuration::get("pigeon_domain")."?psso_auth=".$sso_encoded."&sig=".$signature );
				exit;
			}
		}
	}

	public function addTrial( $customer_id, $plan_number, $trial_days )
	{
		return $this->post("/customer/add_plan", array("customer_id"=>$customer_id,"plan_number"=>$plan_number, "plan_type"=>Pigeon_Plan::ITEM_TYPE_SUBSCRIPTION_TRIAL,"trial_days"=>$trial_days));
	}

	public function switchPlan( $customer_id, $new_plan_number, $send_notice = TRUE )
	{
		return $this->post("/customer/switch_plan", array("customer_id"=>$customer_id,"plan_number"=>$new_plan_number, "send_notice"=>$send_notice ));
	}
}

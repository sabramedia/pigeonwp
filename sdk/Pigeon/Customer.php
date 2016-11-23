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
		if( isset($response->customer->id) )
			$this->customer_id = $response->customer->id;

		return $response;
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

	public function sessionLogout( $customer_id )
	{
		$unique_session = md5(Pigeon_Configuration::get("pigeon_domain"));
		if( array_key_exists($unique_session."_id", $_COOKIE) && array_key_exists($unique_session."_hash", $_COOKIE) ){
			$this->customer_id = NULL;
			return $this->post("/customer/session_logout", array("customer_id"=>$customer_id,"session_id"=>$_COOKIE[$unique_session."_id"],"session_hash"=>$_COOKIE[$unique_session."_hash"]));
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
		return "https://" . Pigeon_Configuration::get("pigeon_domain")."?psso=".$sso_encoded."&sig=".$signature;
	}

	public function addTrial( $customer_id, $plan_number, $trial_days )
	{
		return $this->post("/customer/add_plan", array("customer_id"=>$customer_id,"plan_number"=>$plan_number, "plan_type"=>Pigeon_Plan::ITEM_TYPE_SUBSCRIPTION_TRIAL,"trial_days"=>$trial_days));
	}
}

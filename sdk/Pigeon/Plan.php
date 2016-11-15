<?php

class Pigeon_Plan extends Pigeon
{

	// Matches CMC_Item::TYPE_*** constant in API Server
	const ITEM_TYPE_SUBSCRIPTION = 1;
	const ITEM_TYPE_SUBSCRIPTION_TRIAL = 2;
	const ITEM_TYPE_RESOURCE = 3;
	const ITEM_TYPE_TANGIBLE = 4;
	const ITEM_TYPE_TEMP_PASS = 5;
	const ITEM_TYPE_CREDIT = 6;
	const ITEM_TYPE_COMPLIMENTARY_ACCESS = 7;

	public function find( $plan_id )
	{
		return parent::get("/plan", array("id"=>$plan_id));
	}

	public function search( $filters )
	{
		if( !is_array($filters) )
			$filters = array("search"=>$filters);

		return parent::get("/plan/search", $filters);
	}

	public function getAll()
	{
		return $this->search("");
	}

	public function getTrials()
	{
		return $this->search(array("item_type"=>self::ITEM_TYPE_SUBSCRIPTION_TRIAL));
	}

	public function getPlans()
	{
		return $this->search(array("item_type"=>self::ITEM_TYPE_SUBSCRIPTION));
	}
}

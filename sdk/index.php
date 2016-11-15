<?php
ini_set( "display_errors", TRUE );
require_once( __DIR__. "/Pigeon.php");

Pigeon_Configuration::clientId("sabramedia");
Pigeon_Configuration::apiKey("laksDet74gsdfg");
Pigeon_Configuration::pigeonDomain("pigeon.katapy.com");
//Pigeon_Configuration::pigeonDomain("staging.sabramedia.com");

$pigeon = new Pigeon();


// CREATE NEW ACCOUNT

/**
 * Minimum required is email
 * Did you want to automatically set the login for the customer?
 */
//$response = $pigeon->Customer->create(array(
//	"email"=>"you@someemail.com"
//));


//$response = $pigeon->Customer->find("tt50moqybputon6o1");  // WAPACK admin@sabramedia.com acct
//$response = $pigeon->Customer->find("6e4a8focoe8jd0ln1");  // Staging nick@sabramedia.com acct
//$response = $pigeon->Customer->addTrial("6e4a8focoe8jd0ln1","01-monthly-print","30");

$response = $pigeon->Customer->find("snknysbon0wssku97");  // Staging nick@sabramedia.com acct
//$response = $pigeon->Customer->search(array("search"=>"nick","limit"=>1));
//$response = $pigeon->Customer->sessionLogin("6e4a8focoe8jd0ln1");
//$response = $pigeon->Customer->sessionLogout("6e4a8focoe8jd0ln1");
//$response = $pigeon->Customer->search("nick");

// ATTEMPT TO CREATE CUSTOMER WHEN EMAIL ALREADY EXISTS

//$response = $pigeon->Customer->create(array(
//	"email"=>"admin@sabramedia.com"
//));


// LOGIN PROCESS
//$response = $pigeon->Customer->login("admin@sabramedia.com","Sm112358!");
//
//if( $response->success ){
//	// Log the token and user id for future reference
//	echo $response->customer->display_name;
//	echo " Logged In";
//}else{
//	// Handle failed login here
//	echo $response->error_message;
//}

//$response = $pigeon->Customer->logout("23c66491e798a3370010a6758d6a1837");

//$response = $pigeon->Customer->find(array("token"=>"22b46acc403af624e69c75f7886a0bdf"));

//echo $pigeon->Customer->getSSOLink("nsfvemp8673xpxte382","https://pigeon.katapy.com/test#online-only?t=asdff");
$response = $pigeon->Plan->getPlans();
$response = $pigeon->Customer->addTrial("snknysbon0wssku97",$response->results[0]->items[0]->number,"30");

echo "\n\n\n #===== RESPONSE ====#\n\n";
print_r($response);

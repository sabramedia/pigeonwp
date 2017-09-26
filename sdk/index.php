<?php
ini_set( "display_errors", TRUE );
require_once( __DIR__. "/Pigeon.php");

//Pigeon_Configuration::pigeonDomain("staging.sabramedia.com");
//Pigeon_Configuration::clientId("sabramedia");
//Pigeon_Configuration::apiKey("laksDet74gsdfg");

//Pigeon_Configuration::pigeonDomain("my.lionmountain.tv");
//Pigeon_Configuration::clientId("lionmountain");
//Pigeon_Configuration::apiKey("8n5cl8p1eaa8z9iu");

//Pigeon_Configuration::pigeonDomain("my.cmdtv.org");
//Pigeon_Configuration::clientId("christianmoviesdirect");
//Pigeon_Configuration::apiKey("laksDwgl4vgp8sdfg");

//Pigeon_Configuration::pigeonDomain("pigeon.katapy.com");
//Pigeon_Configuration::clientId("katapy");
//Pigeon_Configuration::apiKey("laksDwgl4vgp8sdfg");

Pigeon_Configuration::pigeonDomain("profil-test.acadienouvelle.com");
Pigeon_Configuration::clientId("acadienouvelle");
Pigeon_Configuration::apiKey("fj3ls285zkq93smx");



//Pigeon_Configuration::pigeonDomain("my.wapacklabs.com");

//Pigeon_Configuration::pigeonDomain("my.thelandondemand.com");
//Pigeon_Configuration::clientId("thelandondemand");
//Pigeon_Configuration::apiKey("d7gjEkSwu4slk9qc");

$pigeon = new Pigeon();


// CREATE NEW ACCOUNT

/**
 * Minimum required is email
 * Did you want to automatically set the login for the customer?
 */
//$response = $pigeon->Customer->create(array(
//	"email"=>"you@someemail.com"
//));

//$response = $pigeon->Customer->getLoggedIn();
////$response = $pigeon->Customer->login("nick@sabramedia.com","qwe123");
////$response = $pigeon->Customer->get();
//if( $response ){
//	echo "success";
//}else{
//	$response = $pigeon->Customer->login("nick@sabramedia.com","qwe123");
//}

//$response = $pigeon->Customer->find("1586mt7i0nekud7s1");
//$pigeon->Customer->SSOLogin("1586mt7i0nekud7s1");
//$response = $pigeon->Customer->find("6e4a8focoe8jd0ln1");  // Staging nick@sabramedia.com acct
//$response = $pigeon->Customer->addTrial("6e4a8focoe8jd0ln1","01-monthly-print","30");

//$response = $pigeon->Customer->find("snknysbon0wssku97");  // Staging nick@sabramedia.com acct

//$response = $pigeon->Customer->search("sabramedia.com");

//$response = $pigeon->Customer->find("1586mt7i0nekud7s1");
//$response = $pigeon->Customer->update("vzca1dem9ildmp7q1",array("display_name"=>"Sabramedia","extend"=>array("platform"=>1)));
//$response = $pigeon->Customer->sessionLogin("6e4a8focoe8jd0ln1");
//$response = $pigeon->Customer->sessionLogout("6e4a8focoe8jd0ln1");
//$response = $pigeon->Customer->search("nick");


// ATTEMPT TO CREATE CUSTOMER WHEN EMAIL ALREADY EXISTS

//$response = $pigeon->Customer->create(array(
//	"email"=>"admin_test1@sabramedia.com"
//));


// LOGIN PROCESS
//$response = $pigeon->Customer->login("nick@sabramedia.com","qwe123");

//`
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

//echo $pigeon->Customer->getSSOLink("6e4a8focoe8jd0ln1","http://staging.sabramedia.com");
//$response = $pigeon->Plan->getPlans();
//$response = $pigeon->Customer->addTrial("snknysbon0wssku97",$response->results[0]->items[0]->number,"30");
$response = $pigeon->Customer->switchPlan("1586mt7i0nekud7s1","13b3f");

echo "\n\n\n #===== RESPONSE ====#\n\n";
print_r($response);

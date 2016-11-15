<?php
require_once( $_SERVER['DOCUMENT_ROOT']."/wp-blog-header.php" );

try{
	$pigeon_values = get_pigeon_values();

	// User must be logged in
	if( ! $pigeon_values["profile"] )
		throw new Exception("No profile");

	$file_url = base64_decode($_GET["auth"]);
	$url_parts = parse_url($file_url);
	$file = $_SERVER["DOCUMENT_ROOT"].$url_parts["path"];
	parse_str($url_parts["query"],$url_query);

	// A customer id is formed with url and must be passed along with url. If they don't match logged in customer id, the fail
	if(array_key_exists("cuid",$url_query) && $url_query["cuid"] != $pigeon_values["profile"]["customer_id"])
		throw new Exception("Customer ID's don't match");



	header("Expires: -1");
	header("Cache-Control: no-cache");
	header("Content-Description: File Transfer");
	header("Content-Length: " . filesize($file));
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=".basename($file));
	readfile($file);
}catch ( Exception $e ){
	echo 'You are not authorized to access this file. <a href="'.home_url().'">I\'m done here</a>.';
}
exit;
?>
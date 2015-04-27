<?php


require_once(__DIR__."/config.php");

$client = new Catapult\Client;
$phoneNumbers = new Catapult\PhoneNumbersCollection;
/*
$call = new Catapult\Call(array(
	"from" => $phoneNumbers->listAll()->last()->number,
	"to" =>"sip:atester@Default-Domain2.bwapp.bwsip.io",
	"callbackUrl" => "http://159.100.186.106/bandwidth-sip-registration/callback/atester"
));
*/

$applications = new Catapult\ApplicationCollection;
foreach ($applications->listAll(array("size" => 1000))->get() as $app) {
  
  $app->delete();
}


?>

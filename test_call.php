<?php

require_once(__DIR__."/config.php");

$client = new Catapult\Client;
//$sipDomain  = "sip:nadir@Default-Domain1.bwapp.bwsip.io";
$sipDomain = "+14157893187";
$from = "+13524611536";

$call = new Catapult\Call(array(
  "to" => $sipDomain,
  "from" => $from,
  "callbackUrl" => "http://159.100.186.106/bandwidth-sip-registration/callback/"
));

?>

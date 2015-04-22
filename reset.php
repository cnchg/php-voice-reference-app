<?php
// Reset all incoming/outgoing 
// calls from and to the SIP clients
//
// IMPORTANT
// only use this if you know what your doing as it will
// erase all your endpointsa and active calls!

require_once(__DIR__."/config.php");
$client = new Catapult\Client;
$calls = new Catapult\CallCollection;
$domains = new Catapult\DomainsCollection;
/*
foreach ($calls->listAll(array("size" => 1000))->get() as $call) {
// check its 
// state and hangup when needed
//
  printf("looking up call:");
  echo var_Dump($call);
  if ($call->state == Catapult\CALL_STATES::active) {
    $call->hangup();
  }

}
*/
foreach ($domains->listAll(array("size" => 1000))->get() as $domain) {
  foreach ($domain->listEndpoints()->get() as $endpoint) {
    $endpoint->delete();
  }
  $domain->delete();
}




?>

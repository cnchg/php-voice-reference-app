<?php

/*
 * This file handles callback events
 */

if (empty($_GET['user'])) {
  die('Error: Missing user query parameter.');
}

// load needed functions
require_once(__DIR__."/helpers.php");

// debug
$file = false;
function debug($message) {
    global $file;
    if ($file === false)
        $file = fopen('debug.log', 'a');
    fwrite($file, $message."\n\n");
}

// load user
$User = getUser($_GET['user'], false);

// initialize objects
$client = new Catapult\Client;
$account = new Catapult\Account;

// parse the event
$event = json_decode(file_get_contents('php://input'));
if (empty($event->eventType)) {
  die('Error: Missing event type.');
}

// set callback URL
$callbackUrl = callbackURL($User->userName);

debug("User: " . json_encode($User));
debug("Event: " . json_encode($event));

// We only need to act on the answer event
if ($event->eventType == 'answer') {
  // Only the 1st leg doesn't have a tag
  if (empty($event->tag)) {
    if ($User->phoneNumber == $event->to) {
      // incoming call
      debug("Handle incoming call: call to sip " . $User->endpoint->sipUri);
      $Call = new Catapult\Call($event->callId);
      return $Call->transfer($User->endpoint->sipUri, array(
        'transferCallerId' => $event->from,
        'callbackUrl' => $callbackUrl,
        'tag' => 'transferred'
      ));
    }
    if (strpos($User->endpoint->sipUri, trim($event->from)) !== false) {
      // outgoing call
      debug("Handle outgoing sip call: call to " . $event->to);
      $Call = new Catapult\Call($event->callId);
      return $Call->transfer($event->to, array(
        'transferCallerId' => $event->from,
        'callbackUrl' => $callbackUrl,
        'tag' => 'transferred'
      ));
    }
  }
  debug("Nothing to do!");
}

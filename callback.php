<?php

/*
 * This file handles callback events
 */

if (empty($_GET['user'])) {
  die('Error: Missing user query parameter.');
}

// load needed functions
require_once(__DIR__."/create.php");

// load user
$User = getUser($_GET['user']);

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

switch ($event->eventType) {
  case "incomingcall":
    if ($User->phoneNumber == $event->to) {
      // incoming call
      //debug("Handle incoming call: call to sip %s", user.endpoint.sipUri);
      return new Catapult\Call(array(
        "from" => $User->phoneNumber,
        "to" => $User->endpoint->sipUri,
        "callbackUrl" => $callbackUrl,
        "tag" => $event->callId
      ));
    }
    if (strpos($User->endpoint->sipUri, trim($event->from)) !== false) {
      // outgoing call
      //debug("Handle outgoing call: call to  %s", $event->to);
      return new Catapult\Call(array(
        "from" => $User->phoneNumber,
        "to" => $event->to,
        "callbackUrl" => $callbackUrl,
        "tag" => $event->callId
      ));
    }
    break;
  case "answer":
    if (!$event->tag) {
      break;
    }
    $Call = new Catapult\Call($event->tag);
    if ($Call->bridgeId) {
      break;
    } elseif($Call->state != "active") {
      $Call->accept();
    }
    $Call->bridgeWith($event->callId);
      /*.then(function(bridge) {
        bridges[$event->callId] = bridge.id;
        bridges[$event->tag] = bridge.id;
      });*/
    break;
  /*case "hangup":
    var bridgeId = bridges[$event->callId];
    if (!bridgeId) {
      return Promise.resolve();
    }
    return Bridge.get(bridgeId)
      .then(function(bridge) {
        return bridge.getCalls();
      })
      .then(function(calls) {
        return Promise.all(calls.map(function(c) {
          delete bridges[c.id];
          if (c.state === "active") {
            debug("Hangup another call");
            return c.hangUp();
          }
        }));
      });
    break;*/
}

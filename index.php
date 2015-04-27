<?php

// SIP call processing using Bandwidth.com API
//
// this examples shows how to implement 
// 
// http://ap.bandwidth.com/docs/how-to-guides/use-endpoints-make-receive-calls-sip-clients/
//
//
// Helpers:
// 1. To distinguish PSTN from registrar
// use type Catapult SIP with isValid/0
//
// 2. To figure out if a number is registered
// under your account use Catapult\PhoneNumbers
// with isEmpty/1
//
//
// This can be called as follows:
//
//
// {your_server}.tld/callback/{username}
// OR 
// {your_server}.tld/users/
// with headers "username={your_username}" "password={your_password}":
//
// make sure you call /users/
// before using the /callback/
require_once(__DIR__."/config.php");
require_once(__DIR__."/create.php");

try {
  // go through our creation process
  // more on this in create.php
  //
  // our username and password
  // will be found in the header
  // 
  // FIXME
  // this url is publicly accessible
  // optionally look for the auth 
  // 
  $headers = getallheaders();
  if (isset($headers['username']) && isset($headers['password']) && !isset($_REQUEST['callback'])) {
    $result = createIfNeeded($headers['username'], $headers['password']);
    // when the result is false
    // we should exit
    // 
    // as our SIP client was not setup properly

    if (is_int($result) && $result == SIP_APPLICATION_SCRIPT_ERROR) {
      // something is not right with our script
      printf("Something went wrong in storing your user contents, make sure they are properly encoded, for more info please view", GITHUB_URL);
    } elseif (is_int($result) && $result == SIP_APPLICATION_SERVER_ERROR) {
      // something went wrong in one of the catapult requests
      printf("SIP client was not setup correct, please change your credentials or view our docs at: %s", SIP_DOCS);

    } elseif (is_int($result) && $result == SIP_APPLICATION_USER_FOUND_WRONG_PASSWORD) {
      printf("The user %s was already registered this password is not correct", $headers['username']);

    } else {
      // send our headers and information
      // the creation was a success
      //
      //header("location: ");
      
      echo json_encode($result);

    }
  } else {
    // This segment handles
    // our callbacks these will
    // only work once the application
    // has been setup
    //
    //
    // url should be as follows:
 
    // https|http://{your_server}.tld/bandwidth-sip-application/callback/{username}
    //

    // check which user
    // is making this call from the username
    //
    $user = getUser($_REQUEST['username']);

    // Our two state events
    // these will both be used in creating
    // this application
    //
    // our answer which  listens
    // to registrar 
    
    $client = new Catapult\Client;
    $answerCallEvent = new Catapult\AnswerCallEvent;
    $incomingCallEvent = new Catapult\IncomingCallEvent;
    $speakCallEvent = new Catapult\SpeakCallEvent;
    // Standard execution:
    //
    // we need to check whether 
    // a PSTN is making the call or 
    // whether the call is being made
    // by our SIP client. The switch on
    // this will validate what's needed
    // as per our incoming and answer call
    // events

    // say thank you
    // this needs to 
    // be an SIP endpoint
    if ($answerCallEvent->isActive()) {
       $sip = new Catapult\SIP($answerCallEvent->to);
       // while we check 
       // if the call is going
       // to the default endpoint
       // our first check is sufficient
       // for an sip client call
       if ($sip->isValid()) {
        $call = new Catapult\Call($answerCallEvent->callId);
        $call->speakSentence(array(
          "voice" => "Kate",
          "sentence" => "Hello SIP client"
        ));
       } else {

        // handle our reverse flow
        // this is is sip to PSTN
        // our call has been answered

        // this means we can
        // bridge our two calls
        $call = new Catapult\Call($answerCallEvent->callId);
        // find our which was
        // set with the incoming call event
        //
        // initial call
        $otherCollection = new Catapult\CallCollection; 
        $otherCall= $otherCollection->listAll()->find(array("to" => $answerCallEvent->to))->last();
      
        // use our other call id
        // in this bridging
        $bridge = new Catapult\Bridge(array(
          "callIds" => array($call->id, $otherCall->id)
        ));
        // we should
        // have a bridged
        // call  now
       } 
    }
    // handle our speak event
    // we will end the call when
    // this is set to stopped
    if ($speakCallEvent->isActive()) {
      if ($speakCallEvent->state == Catapult\SPEAK_STATES::stopped) {
        // speech is done
        // make sure its an SIP client again
        $call = new Catapult\Call($speakCallEvent->callId);
        $sip = new Catapult\SIP($call->to);
        if ($sip->isValid()) {
          $call = new Catapult\Call($speakCallEvent->callId);
          $call->hangup();
        }
      }
    }
    // handle incoming requests
    // NOTE:
    //
    // this application will be autoAnswer by default
    // in order to activate the sequence below
    // you will need to set autoAnswer = false
    //
     if ($incomingCallEvent->isActive()) {

     $sip = new Catapult\SIP($incomingCallEvent->from);
     if ($sip->isValid()) {
       // good, we can answer our 
       // call
        $call = new Catapult\Call($incomingCallEvent->callId);
        if ($call->state == Catapult\CALL_STATES::started) {
          $call->answer();
        }
        // using our other PSTN number
        // we can create a call to this 'to'
        // pstn
        $newCall = new Catapult\Call(array(
          "from" => $otherNumber,
          "to" => $incomingCallEvent->to 
        ));
      }
    } 
  }
} catch (CatapultApiException $e) {
  $error = $e->getResult();
  // let's log this
  // attempt
  //

}
?>

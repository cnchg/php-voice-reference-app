<?php

/*
 * This file receives a userName from index.html
 * and procedes with setting up the call demo
 */

if (empty($_POST['userName'])) {
  die('Error: This file should not be called directly. Missing POST data.');
}

// turn off error display
ini_set('display_errors', '0');

// load needed functions
require_once(__DIR__."/helpers.php");

// create the user, if it doesn't already exists
$User = createIfNeeded($_POST['userName']);

// make sure everything is an array
$User = json_decode(json_encode($User), true);

if ((!is_array($User)) || (empty($User['endpoint']))) {
  die('Error: Failed to create / load the endpoint: '.$User);
}

// now create a new auth token to be used
$Token = createAuthToken($User['endpoint']['domainId'], $User['endpoint']['id']);
if (!is_object($Token)) {
  die('Error: Failed to create and auth token: '.$Token);
}

$webrtcEnv = "";
if (defined('ENVIRONMENT') && ENVIRONMENT != "prod") {
  $webrtcEnv = "-" . ENVIRONMENT;
}

// make sure everything is an array
$Token = json_decode(json_encode($Token), true);

$ret = array(
  '{{username}}' => $User['userName'],
  '{{password}}' => '',
  '{{authToken}}' => $Token['token'],
  '{{authTokenDisplayData}}' => json_encode(array(
    'token' => $Token['token'],
    'expires' => $Token['expires']
  )),
  '{{userData}}' => json_encode($User),
  '{{phoneNumber}}' => $User['phoneNumber'],
  '{{webrtcEnv}}' => $webrtcEnv,
  '{{domain}}' => $User['endpoint']['credentials']['realm']
);

$calldemo = str_replace(array_keys($ret), array_values($ret), file_get_contents('calldemo.html'));

echo $calldemo;
exit;

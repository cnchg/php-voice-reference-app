<?php

/**
 * check if the application is
 * currently configured
 *
 */

require_once(__DIR__."/config.php");

try {
  $client = new Catapult\Client;
  $account = new Catapult\Account;
  $account->get();
  // make sure users and config.json
  // are proper json files
  $files = array( "users.json", "credentials.json");
  foreach ($files as $file) {
    $obj = json_decode(file_get_contents(__DIR__ . $file));
    if (!is_object($obj) && !is_array($obj)) {
      throw new Exception(sprintf("The file: %s is not properly encoded", $file));
    }
  }

  printf("Thanks you can use the SIP Call Processing application\n");
} catch ( CatapultApiException $e) {
  printf("Error: You  need to set up your credentials..");
}


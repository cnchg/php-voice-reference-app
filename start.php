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

  printf("Thanks you can use the SIP Call Processing application\n");
} catch ( CatapultApiException $e) {
  printf("Error: You  need to set up your credentials..");
}


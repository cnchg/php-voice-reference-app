<?php

require_once(__DIR__."/vendor/autoload.php");

define("SIP_DOCS", "http://ap.bandwidth.com/docs/how-to-guides/use-endpoints-make-receive-calls-sip-clients/");
define("GITHUB_URL", "https://github.com/BandwidthExamples/php-voice-reference-app");
define("ENVIRONMENT", "stage"); // "stage" or "prod"

$application = (array) json_decode(__DIR__."/application.json");

// since domain names are unique across the whole platform
// we'll generate a new random string and use it for this
// installation, if one has not already been provided

if (!file_exists(__DIR__."/domain.json")) {
  // generate a random string between 6 and 10 chars
  $domain = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, mt_rand(6, 10));
  file_put_contents(__DIR__."/domain.json", $domain);
} else {
  $domain = trim(file_get_contents(__DIR__."/domain.json"));
}

define("DEFAULT_DOMAIN_NAME", $domain);

<?php

require_once(__DIR__."/vendor/autoload.php");

define("SIP_DOCS", "http://ap.bandwidth.com/docs/how-to-guides/use-endpoints-make-receive-calls-sip-clients/");
define("GITHUB_URL", "https://github.com/BandwidthExamples/php-voice-reference-app");
define("ENVIRONMENT", "stage"); // "stage" or "prod"

$application = (array) json_decode(__DIR__."/application.json");

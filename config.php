<?php

require_once(__DIR__."/php-bandwidth/source/Catapult.php");

define("SIP_DOCS", "http://ap.bandwidth.com/docs/how-to-guides/use-endpoints-make-receive-calls-sip-clients/");
define("GITHUB_URL", "https://github.com/BandwidthExamples/php-voice-reference-app");

$application = (array) json_decode(__DIR__."/application.json");

Catapult\Credentials::setPath(__DIR__);
?>

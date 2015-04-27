<?php
// we need this for nginx based heroku 
// deploys
//

if (!function_exists("getallheaders")) {
  function getallheaders() {
    $headers = $_SERVER;
    $finalHeaders = array();
    foreach ($headers as $k => $header) {
      if (preg_match("/^HTTP_(.*)/", $k, $match)) {

        $finalHeaders[strtolower($match[1])] = $header;  
      }
    }

    return $finalHeaders;
  }

}
?>

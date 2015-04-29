<?php


// SIP call processing using Bandwidth.com SIP
//
//
// this file  implements what we need in order to use
// domains and endpoints. Here we will create the following:
//
// 1. Catapult User
// 2. Catapult endpoint and domain
//
// we also need to check if any of this
// has already been done or not.
// we can do this using our default domain

/** add seperate applications for the user, with callbacks **/ 
/** names should look like 'SIP Client Application [userName]' **/
define("DEFAULT_APPLICATION_NAME", "SIP Client Application ");
define("DEFAULT_DOMAIN_NAME", "Default-Domain");
define("DEFAULT_DOMAIN_DESCRIPTION", "a unique description");
define("DEFAULT_ENDPOINT_DESCRIPTION", "a unique endpoint description");
define("DEFAULT_AREA_CODE", "469");
define("DEFAULT_ACCOUNT_NAME", "");
/** error from our script **/
define("SIP_APPLICATION_SCRIPT_ERROR", -1);
/** error from catapult server **/
define("SIP_APPLICATION_SERVER_ERROR", -2);
define("SIP_APPLICATION_USER_CREATED", 1);
define("SIP_APPLICATION_USER_NOT_FOUND", 0);
define("SIP_APPLICATION_USER_FOUND_WRONG_PASSWORD", -3);
define("SIP_APPLICATION_PHONE_NUMBER_NOT_FOUND", -4);
define("DEFAULT_USERS_FILE", "users.json");
define("DEFAULT_CONFIG_FILE", "config.php");

require_once(__DIR__."/config.php");
function createIfNeeded($userName='', $password='', $domainName=DEFAULT_DOMAIN_NAME, $domainDescription=DEFAULT_DOMAIN_DESCRIPTION, $endpointDescription=DEFAULT_ENDPOINT_DESCRIPTION, $areaCode=DEFAULT_AREA_CODE, $applicationName=DEFAULT_APPLICATION_NAME) {
  try {
    $client = new Catapult\Client;
    $account = new Catapult\Account; 

    // first let's retrieve or create
    // a new application
    //
    // TODO: currently supports a single user
    // this should be multiuser based
    $newUser = addUserIfNeeded($userName, $password);
    if ($newUser == SIP_APPLICATION_USER_CREATED) {
      // this means
      // our user was already
      // created and this is a 
      // 
      // subsequent request, we
      // can find all our data in users.json
      return getUser($userName); 
    } elseif ($newUser == SIP_APPLICATION_USER_FOUND_WRONG_PASSWORD) {
      // our additional request
      // was found with the wrong
      // userName, password
      return SIP_APPLICATION_USER_FOUND_WRONG_PASSWORD; 
    }

    // seperate the applicationName from the other
    // users by userName
    $applicationName .= " [" . $userName . "]";
    $applications = new Catapult\ApplicationCollection;
    $applications = $applications->listAll(array("size" => 1000))->find(array(
       "name" => $applicationName
    ));
    if ($applications->isEmpty()) {
      // standards for creating
      // this application provided
      // we're using SIP
      //
      // callbackMethod 
      //
      $application = new Catapult\Application(array(
        "name" => $applicationName, 
        "callbackHttpMethod" => "POST",
        // our callbacks will
        // be triggered to this
        // page
        //
        // as we are using PHP_SELF
        // add the userName in our callback
        "incomingCallUrl" => "http://" . $_SERVER{"HTTP_HOST"} . preg_replace("/\/.*/", "", $_SERVER{'PHP_SELF'}) . "/"  . sprintf("callback/%s", $userName),
        "autoAnswer" => TRUE
      ));
    } else {
      $application = $applications->last();
    }

    // now we can check 
    // for domains under this
    // account which should
    // be similar to the user
    // check
    $domains = new Catapult\DomainsCollection;
    $domains = $domains->listAll(
      array(
          "size" => 1000
      )
    )->find(array(
      "name" => $domainName
    ));
    

    // have we created a domain     
    // for this name, if we have
    // lets use it
    if (!$domains->isEmpty()) {
      // application was 
      // already ran with this
      // no need to create
      $domain = $domains->last();      

      // due to whatever reason (an HTTP request failing)
      // or a client exiting prematuraly we should still
      // check if this endpoint was made
      // we can do this by listing all
      // the endpoints for this domain

      $endpoint = $domain->listEndpoints()->find(array("name"=>$userName));
      if ($endpoint->isEmpty()) {
        // here we create
        // the endpoint

        $endpoint = new Catapult\Endpoints($domain->id,array(
          "name"=> $userName, 
          "applicationId" => $application->id,
          "description" => $endpointDescription,
          "credentials" => array(
            "username" => $userName,
            "password" => $password
          )
        ));
      }  else {
        // point to the last
        // endpoint
        //
        $endpoint = $endpoint->last();
      }
    } else {
      // create our default
      // domain
      //
      // The name on this easily identifiable
      // is our definition, which can be
      // found at the top of this program
      $domain = new Catapult\Domains(array(
         "name" => $domainName,
         "description" => $domainDescription
      ));
      $endpoint = new Catapult\Endpoints($domain->id,array(
        "name"=> $userName, 
        "applicationId" => $application->id,
        "credentials" => array(
          "userName" => $userName,
          "password" => $password
        )
      ));
    }
    
    // we now need to allocate one number
    // using our default area code
    // 
    // when an application already has a phone 
    // number this process is not needed 

    // we can find out using the return value
    // of our initial user check
    // try to find numbers in our default
    // area code
    $phoneNumbers = new Catapult\PhoneNumbers;
    $phoneNumber = $phoneNumbers->listLocal(array(
      "areaCode" => $areaCode
    ));
 
    // check if we have any numbers 
    // no numbers should result in warning 
    if (!$phoneNumber->isEmpty()) {
      
      // try to allocate the last found number
      $allocatingNumber = $phoneNumber->last();
      $phoneNumber = $phoneNumbers->allocate(array(
        "number" =>  $allocatingNumber->number,
        "applicationId" => $application->id
      ));

      // add a user to the
      // users.json
      $addedUser = addUser($userName, $password, $domain, $endpoint, $phoneNumber->number);
      // only when we get a good
      // response we will return
      // otherwise bring back as file i/o warning
      $endpointArray = $endpoint->toArray();
      unset($endpointArray['domains']);
      if ($addedUser) {
        return array(
          "userName" => $userName,
          //"password" => $password,
          "endpoint" => $endpointArray,
          //"domain" => $domain->toArray(),
          "phoneNumber" => $phoneNumber->number
        );
      }  
    } else {
        // no numbers were
        // found return
        // 
        return SIP_APPLICATION_PHONE_NUMBER_NOT_FOUND;
    }

  } catch (CatapultApiException $e) {
    // something happened
    // we should check
    
    $error = $e->getResult();
    // server errors will be handled
    return $error;
  }

  // something went wrong
  // we should warn the user, this is 
  // either file i/o or some internal reason
  return SIP_APPLICATION_SCRIPT_ERROR;
}

// add a user to our
// users.json if we need to
// this should validate the current password
// 
// accept the applicationId, default endpoint and our default number
function addUserIfNeeded($userName='', $password='', $domain=array(), $endpoint=array(), $defaultNumber='') {

  $users = json_decode(file_get_contents(realpath("./users.json")));
  if (sizeof($users)>0) {
    foreach ($users as $user) {
      if ($user->username == $userName && md5($password) == $user->password) {
        return 1;
      }
      if ($user->username == $userName) {
        // TODO we can wrap this around our exception 

        return SIP_APPLICATION_USER_FOUND_WRONG_PASSWORD; //same password was not provided, creating user was tried
      }
    }
  }
  // generic this is a new user

  return 0;
}

// add a user to our
// endpoint records
function addUser($userName, $password, $domain=array(), $endpoint=array(), $defaultNumber) {
  $users = (array) json_decode(file_get_contents(realpath("./") . "/" . DIRECTORY_SEPARATOR . DEFAULT_USERS_FILE));
  // when we're null create
  // a new context
  $users[] = array(
    "uuid" => uniqid(true),
    "username" => $userName,
    "password" => md5($password),
    "phoneNumber" => $defaultNumber,
    "domain" => $domain->toArray(),
    "endpoint" => $endpoint->toArray()
  );
  $res = file_put_contents(realpath("./") . "/" . DEFAULT_USERS_FILE, json_encode($users));
  // make sure we specify our result
  return $res;
}
// once we are authenticated, we can
// use getUser, this should return all our
// data without having to make, lookup catapult 
function getUser($userName='') {
  $users = json_decode(file_get_contents(realpath("./") . "/" . DEFAULT_USERS_FILE));
  if (sizeof($users) > 0) {
    foreach ($users as $user) {
      if ($user->username == $userName) {
        unset($user->domain);
        unset($user->password);
        unset($user->uuid);
        unset($user->endpoint->domains);
        return $user;
      }
    }
  }
  // hardly possible, implementor mistake
  return null;
}
// show an error in json
// so it can be parsed same way
// as a success, this should
// be only for client side errors
function showError($msg) {
  printf(json_encode(array("message" => $msg)));
}

?>

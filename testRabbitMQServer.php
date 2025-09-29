#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');

function doLogin($username,$password)
{
    // lookup username in databas
    // check password
    $login = new loginDB();
    return $login->validateLogin($username,$password);
    //return false if not valid
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    // case "validate_session":
    //   return doValidate($request['sessionId']);
    case "signup":
      return doSignup($request['username'],$request['password']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}
function doSignup($username,$password)
{
    // lookup username in databas
    // check password
    $signup = new loginDB();
    return $signup->registerUser($username,$password);
    //return false if not valid
}
// function doValidate($username,$sessionKey)
// {
//     $validate_session = new loginDB();
//     return $login->storeSessionkey($sessionKey);
// }

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>


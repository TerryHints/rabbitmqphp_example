<?php
require_once "mq_client.php";
session_start();
$error='';

if($_SERVER["REQUEST_METHOD"] === "POST"){
  $username = $_POST['username']; 
  $password = $_POST['password'];
  $response = mq_rpc(['type'=>'login','username'=>$username,'password'=>$password]);

  if(($response['status'] ?? '') === 'ok'){
    $_SESSION['user_id'] = $response['user_id'];
    $_SESSION['username'] = $username;
    $_SESSION['session_key'] = $response['session_key'];
    header("Location: home.php"); 
    exit;

  } 
  elseif(
    ($response['status'] ??'') === 'fail'){ 
      $error="Invalid login."; 
      } 
  else { 
    $error="Login unavailable."; 
  }
}

?>
<html>
<body style="background:lavender; color:white;"> <!--realized webkit doesn't work on firefox-->
<h2>Login</h2>
<form method="post">
  <input name="username" placeholder="Username"><br>
  <input name="password" type="password" placeholder="Password"><br>
  <button>Login</button>
</form>
<p><?= $error ?></p>
<div><a href="register.php"><button type="button">Register</button></a></div>
</body>
</html>

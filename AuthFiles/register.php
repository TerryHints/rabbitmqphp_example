<?php

require_once "mq_client.php";
$error='';

if($_SERVER["REQUEST_METHOD"]==="POST"){
  $username=$_POST['username']; 
  $email=$_POST['email']; 
  $password=$_POST['password'];

  $response=mq_rpc(['type'=>'register','username'=>$username,'email'=>$email,'password'=>$password]);
  
  if(($response['status']??'')==='ok'){ 
    header("Location: index.php"); 
    exit; 
  }
  $error=$response['message']??"Registration failed.";
}
?>

<html>
<body style="background:lavender;  color:white;">
<h2>Register</h2>
<form method="post">
  <input name="username" placeholder="Username"><br>
  <input name="email" type="email" placeholder="Email"><br>
  <input name="password" type="password" placeholder="Password"><br>
  <button>Register</button>
</form>
<p><?= $error ?></p> <!-- If error is empty its just gonna render an empty paragraph -->
</body>
</html>
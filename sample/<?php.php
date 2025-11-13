<?php
$host = "dbvm";
$user = "webuser";
$pass = "12345";
$db   = "tune";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error){
	die("DB connection failed : ".$conn->connect_error);
}
?>

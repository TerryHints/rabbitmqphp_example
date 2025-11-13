#!/usr/bin/php
<?php

$mydb = new mysqli('127.0.0.1','testUser','12345','testdb');

if ($mydb->errno != 0)
{
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
}

echo "successfully connected to database".PHP_EOL;

$startup = "CREATE TABLE users(id INT PRIMARY KEY AUTO_INCREMENT, screenname VARCHAR(255) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, sessionkey VARCHAR(255) UNIQUE NULL);";

#NEW 
$addUser = "INSERT INTO USERS(username, password)
VALUES
('Terry','12345');

$view = "select * from USERS;";
$trueCheck = "select * from USERS WHERE username = 'Terry' AND password = '12345';";
$falseCheck = "select * from USERS WHERE username = 'Terry' AND password = '123';";

$response = $mydb->query($startup);
if ($mydb->errno != 0)
{
	echo "failed to execute query:".PHP_EOL;
	echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	exit(0);
}
if ($response->num_rows>0)
{
	echo "Login Successfull".PHP_EOL;
}  
else{
	echo "Invalid Login".PHP_EOL;
}


?>

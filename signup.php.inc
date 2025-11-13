
<?php

class signupDB
{
private $logindb;

public function __construct()
{
	$this->logindb = new mysqli("127.0.0.1","testUser","12345","testdb");

	if ($this->logindb->connect_errno != 0)
	{
		echo "Error connecting to database: ".$this->logindb->connect_error.PHP_EOL;
		exit(1);
	}
	echo "correctly connected to database".PHP_EOL;
}

public function registerUser($username,$password)
{
	$un = $this->logindb->real_escape_string($username);
	$pw = $this->logindb->real_escape_string($password);
	
	$addUser = "INSERT INTO USERS(username, password)
		VALUES
		('$un','$pw');";
	$response = $this->logindb->query($addUser);

	if($response){
	return 1;
	}
	return 0;
}
}
?>
<?php

class loginDB
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

public function storeSessionkey($username,$sessionkey)
{
	$un = $this->logindb->real_escape_string($username);
	$sk = $this->logindb->real_escape_string($sessionkey);

	$update = "UPDATE users SET session_key = '$sk' WHERE screenname = '$un'";
    $result = $this->logindb->query($update);

	 if ($result) {
        echo "Session key stored for user '$un'".PHP_EOL;
        return 1;
    } else {
        echo "Failed to store session key: " .$this->logindb->error.PHP_EOL;
        return -1;
    }
}

public function validateLogin($username,$password)
{
	$un = $this->logindb->real_escape_string($username);
	$pw = $this->logindb->real_escape_string($password);
	$statement = "select * from users where screenname = '$un'";
	$response = $this->logindb->query($statement);

	while ($row = $response->fetch_assoc())
	{
		echo "checking password for $username".PHP_EOL;
		if ($row["password"] == $pw)
		{
			echo "passwords match for $username".PHP_EOL;
			return 1;// password match
		}
		echo "passwords did not match for $username".PHP_EOL;
	}
	return 0;//no users matched username
}
public function registerUser($username,$password)
{
	$un = $this->logindb->real_escape_string($username);
	$pw = $this->logindb->real_escape_string($password);
	

	$checkUser = "SELECT * FROM users WHERE screenname = '$un'";
    $result = $this->logindb->query($checkUser);

    if (!$result) {
        echo "Error checking for user: " . $this->logindb->error . PHP_EOL;
        return -1;
    }

    if ($result->num_rows > 0) {
        echo "User '$un' already exists." . PHP_EOL;
        return -1; // User already exists, don't insert
    }

    // Step 2: Insert new user
    $addUser = "INSERT INTO users(screenname, password) VALUES ('$un', '$pw')";
    $response = $this->logindb->query($addUser);

    if ($response) {
        echo "User '$un' registered successfully." . PHP_EOL;
        return 1;
    } else {
        echo "Error registering user: " . $this->logindb->error . PHP_EOL;
        return -1;
    }
}
}
?>
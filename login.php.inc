<?php

class loginDB
{
private $logindb;

public function __construct()
{
	//Things to change when we switch 
	//127.0.0.1 -> Ip of where ever new host
	//'testdb' -> name of db we're using
	$this->logindb = new mysqli("127.0.0.1","testUser","12345","tune");

	if ($this->logindb->connect_errno != 0)
	{
		echo "Error connecting to database: ".$this->logindb->connect_error.PHP_EOL;
		exit(1);
	}
	echo "correctly connected to database".PHP_EOL;
}

// /**
//  * Stores a externally generated session id for the user with the given username for later use.
//  */
// public function storesessionId($username,$sessionId)
// {



// 	// create a session key
// 	// save the session key
// 	// tell the user the session key


// 	$un = $this->logindb->real_escape_string($username);
// 	$sk = $this->logindb->real_escape_string($sessionId);

// 	$update = "UPDATE users SET session_key = '$sk' WHERE username = '$un'";
//     $result = $this->logindb->query($update);

// 	 if ($result) {
//         echo "Session key stored for user '$un'".PHP_EOL;
//         return 1;
//     } else {
//         echo "Failed to store session key: " .$this->logindb->error.PHP_EOL;
//         return -1;
//     }
// }

// /**
//  * It's now later use!  Check that the session id is correct for the given username and validate it.
//  */
// public function validateSession($username, $sessionId)
// {

// }

/**
 * TRUE AND FALSE, FINDS USER AND CHECKS PASSWORD.  THREE NORMAL CONDITIONS :
 IF USER EXISTS AND PASSWORD WRONG : FALSE
 IF USER EXISTS AND PASSWORD RIGHT : TRUE
 IF USER DOESN'T EXIST FALSE
 ERROR CONDITION :
 IF QUERY FAILS : -1
 */
public function validateLogin($username,$password)
{
	// Prepare the query
	$un = $this->logindb->real_escape_string($username);
	$pw = $this->logindb->real_escape_string($password);
	$statement = "select * from users where username = '$un'";

	// Submit the query
	$response = $this->logindb->query($statement);

	if (!$response) {
        echo "Error checking for user: " . $this->logindb->error . PHP_EOL;
        return -1;
    }

	if ($row = $response->fetch_assoc()) {
		// Screenames should be unique; clearly we messed up somewhere
		assert(is_null($response->fetch_assoc()));

		echo "checking password for $username".PHP_EOL;
		if (password_verify($pw, $row["password_hash"]))
		{
			echo "passwords match for $username".PHP_EOL;

			// User is now logged in!

			// Generate session key
			$sessionkey = bin2hex(random_bytes(32));


			// TODO Determine ended_at time

			$id = $row['id'];

			$addsession = "INSERT INTO user_sessions(user_id, session_key) VALUES ('$id', '$sessionkey')";
		    $response = $this->logindb->query($addsession);
		    //Check query response

		    if ($response) {
		        echo "Session for User '$un' registered successfully." . PHP_EOL;
		        return 1;
		    } else {
		        echo "Error registering user session : " . $this->logindb->error . PHP_EOL;
		        return -1;
		    }
		}
		echo "passwords did not match for $username".PHP_EOL;
	}
	else {
		echo "User failed password check";
		// No users matched username
		return 0;
	}
}

/**
 * Adding a user.  
 - First checks whether database rows alligns with whatever db we're using.
 - Checks for users :
 If user exists, RETURN 0
 If doesn't exist, add user, RETURN 1 (side effect)
 Database failure, RETURN -1
 */
public function registerUser($username,$password)
// TODO add args for email, etc.
{
	//Create and submit the query
	$un = $this->logindb->real_escape_string($username);
	$pw = $this->logindb->real_escape_string($password);
	

	$checkUser = "SELECT * FROM users WHERE username = '$un'";
    $result = $this->logindb->query($checkUser);

    if (!$result) {
        echo "Error checking for user: " . $this->logindb->error . PHP_EOL;
        return -1;
    }

    // at this point, num_rows can be anything

    if ($result->num_rows > 0) {
    	// If this fails, our database is set up incorrectly or a table has been altered without us knowing
    	assert($result->num_rows == 1);

        echo "User '$un' already exists." . PHP_EOL;
        return 0; // User already exists, don't insert
    }

    // Hash the password
    $hash = password_hash($pw, PASSWORD_DEFAULT);

    //Insert new user
    $addUser = "INSERT INTO users(username, password_hash, email) VALUES ('$un', '$hash', 'examlple@example.com')";
    $response = $this->logindb->query($addUser);
    //Check query response

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
<?php include "session_check.php"; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Home</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>You are logged in to the Spotify-clone project.</p>
    <a href="logout.php"><button>Logout</button></a>
  </div>
</body>
</html>
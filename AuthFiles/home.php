<?php
session_start();
if(empty($_SESSION['username'])){
  header("Location: index.php");
  exit;
}
?>
<html>
<body style="background:lavender; color:white;">
<h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
<p>You are logged in.</p>
<a href="logout.php"><button>Logout</button></a>
</body>
</html>
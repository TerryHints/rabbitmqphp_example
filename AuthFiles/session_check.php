<?php
session_start();

if(empty($_SESSION['user_id']) || empty($_SESSION['session_key'])){
  header("Location: index.php");
  exit;
}
?>

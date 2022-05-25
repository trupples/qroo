<?php

session_start();
if(!isset($_SESSION['user'])) {
	session_destroy();
	header("Location: /login.php");
	die("You need to be <a href='/login.php'>logged in</a>!");
}

?>

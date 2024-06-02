<?php
session_start();
        $_SESSION['first'] = $_POST['first'];
        $_SESSION['last'] = $_POST['last'];
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['password'] = $_POST['password'];
        $_SESSION['height'] = $_POST['height'];
        $_SESSION['weight'] = $_POST['weight'];
	$_SESSION['movie'] = $_POST['movie'];
        $_SESSION['color'] = $_POST['color'];
	include 'write.php';
        include 'read.php';
?>

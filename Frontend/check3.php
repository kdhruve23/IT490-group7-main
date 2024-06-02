<?php
        session_start();
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['password'] = $_POST['password'];
	$_SESSION['movie'] = $_POST['movie'];
	$_SESSION['color'] = $_POST['color'];
        include 'write3.php';
        include 'read3.php';
?>

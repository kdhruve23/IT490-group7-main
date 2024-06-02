<?php
        session_start();
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['password'] = $_POST['password'];
        $email = $_SESSION['email'];
        include 'write2.php';
        include 'read2.php';
?>

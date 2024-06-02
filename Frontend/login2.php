<?php
        session_start();
        $_SESSION['first'] = $_POST['first'];
        $_SESSION['last'] = $_POST['last'];
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['password'] = $_POST['password'];
        $email = $_SESSION['email'];
        //include 'write.php';
?>
<?php
	// Check if there is an error parameter indicating no success
	if (isset($_GET['error']) && $_GET['error'] === 'no_success') {
    		echo '<script>alert("Login Unsuccessful!");</script>';
	}
	else if (isset($_GET['error']) && $_GET['error'] === 'timeout') {
                echo '<script>alert("Timeout!");</script>';
	}
	else if (isset($_GET['pass_error']) && $_GET['pass_error'] === 'no_success') {
                echo '<script>alert("Could not reset password!");</script>';
	}
	else if (isset($_GET['pass']) && $_GET['pass'] === 'success') {
                echo '<script>alert("Password successfully reset!");</script>';
        }
	else if(isset($_GET['success']) && $_GET['success'] === 'true'){
                echo '<script>alert("Registration Successful!");</script>';
        }

?>

<html>
<head>
    <title>ShapeShift</title>
    <link rel="stylesheet" href="index.css">
</head>
<body style="color:#008080;">
    <h1 class="welcome">
        <img class="img1" src="logo.png">
        LOGIN TO SHAPESHIFT <br>
        <p class="mantra">SHIFT YOUR HEALTH, SHAPE YOUR FUTURE</p>
        <h1 class="register">LOGIN</h1>
    </h1>
    <form action="check2.php" method="post" style="font-size: 23px; padding-left: 385px;">
        <label for="email"><b>Email</b></label>
        <input type="text" placeholder="Enter Email" name="email" id="email" required>
        <br><br>
        <label for="password"><b>Password</b></label>
        <input type="password" placeholder="Enter Password" name="password" id="password" required>
	<br>
	<a style="font-size: 18px" href="forgot_password.php">Forgot Password?</a>
	<br><br>
        <button type="submit" class="button1">Login</button>
    </form>
</body>
</html>
<script>
        console.log($email);
</script>

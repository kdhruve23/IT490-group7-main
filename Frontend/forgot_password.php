<?php
        session_start();
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['password'] = $_POST['password'];
?>

<html>
<head>
    <title>ShapeShift</title>
    <link rel="stylesheet" href="index.css">
</head>
	<body style="color:#008080;">
    		<h1 class="welcome">
        		<img class="img1" src="logo.png">
        		SHAPESHIFT: PASSWORD RESET <br>
        		<p class="mantra">SHIFT YOUR HEALTH, SHAPE YOUR FUTURE</p>
        		<h1 class="register">Forgot Password</h1>
    		</h1>
		<form method="post" style="padding-left: 375px; font-size: 23px" action="check3.php">
    			<label for="email"><b>Email:</b></label>
    			<input type="email" id="email" name="email" required>
    			<br><br>
    		
        		<label for="security_answers"><b>Favorite Movie: </b></label>
        		<input type="text" id="movie" name="movie" required>
			<br><br>

			<label for="security_answers"><b>Favorite Color: </b> </label>
                        <input type="text" id="color" name="color" required>
                        <br><br>
			
			<label for="security_answers"><b>New Password: </b> </label>
                        <input type="password" id="password" name="password" required>
                        <br><br>

			<input class="button1"  type="submit" value="Submit">
		</form>

	</body>
</html>

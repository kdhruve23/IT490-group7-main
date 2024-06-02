<?php
	//echo "ngbj";
        session_start();
        $_SESSION['first'] = $_POST['first'];
        $_SESSION['last'] = $_POST['last'];
        $_SESSION['email'] = $_POST['email'];
	$_SESSION['password'] = $_POST['password'];
	$_SESSION['height'] = $_POST['height'];
	$_SESSION['weight'] = $_POST['weight'];
	$email = $_SESSION['email'];
	$_SESSION['movie'] = $_POST['movie'];
	$_SESSION['color'] = $_POST['color'];
?>
<?php
	// Check if there is an error parameter indicating no success
	if (isset($_GET['error']) && $_GET['error'] === 'no_success') {
    		echo '<script>alert("Registration Unsuccessful!");</script>';
	}
?>
<html>
        <head>
                <title> ShapeShift </title>
                <link rel="stylesheet" href="index.css" />
                <script>
                        function validate(){
                                var a = document.getElementById("password").value;
                                var b = document.getElementById("password-repeat").value;
                                if (a!=b) {
                                        alert("Passwords do no match");
                                        return false;
                                }       
                        }
                </script>
        </head>

        <body style="color:#008080;" >
                <h1 class="welcome">
                        <img class="img1" src="logo.png">
                        WELCOME TO SHAPESHIFT <br>
                        <p class="mantra">SHIFT YOUR HEALTH, SHAPE YOUR FUTURE</p>
                        <h1 class="register"> REGISTRATION </h1>
                        <p align="center">Already have an account?<a href="/login2.php"> Login here </a></p>
                </h1>
                <form onsubmit="return validate();" action="/check.php" method="post" style="font-size: 23px; padding-left: 370px;">
                        <label for="first"><b>First Name</b></label>
                        <input type="text" placeholder="Enter First Name" name="first" id="first" required>
                <br><br>
                        <label for="last"><b>Last Name</b></label>
                        <input type="text" placeholder="Enter Last Name" name="last" id="last" required>
			
		<br><br>
			<label for="last"><b>Height</b></label>
                        <input type="number" placeholder="65" name="height" id="height" required> Inches

                <br><br>
			<label for="last"><b>Weight</b></label>
                        <input type="number" step="any" placeholder="156" name="weight" id="weight" required> lbs

                <br><br>
                        <label for="email"><b>Email</b></label>
                        <input type="text" placeholder="Enter Email" name="email" id="email" required>
                <br><br>
                        <label for="password"><b>Password</b></label>
                        <input type="password" placeholder="Enter Password" name="password" id="password" required>
                <br><br>
                        <label for="password-repeat"><b>Repeat Password</b></label>
                        <input type="password" placeholder="Repeat Password" name="password-repeat" id="password-repeat" required>
		<br><br>
			<label for="movie"><b>Favorite Movie: </b></label>
                        <input type="text" placeholder="Back to the Future" name="movie" id="movie" required>
                <br><br>
			<label for="color"><b>Favorite Color: </b></label>
                        <input type="text" placeholder="Red" name="color" id="color" required>
                <br><br>

                        <button type="submit" class="button1">Register</button>
                </form>
        <body>
</html>

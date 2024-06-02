<?php
	session_start();
	$user_data = $_SESSION['user_data'];
        $email = $user_data['email'];
        $weight = $user_data['weight'];
	$currentWeight = ($weight > 0) ? $weight : "N/A";
?>
<html>
        <head>
                <title> ShapeShift </title>
                <link rel="stylesheet" href="/index.css" />
        </head>

        <body style="color:#008080;" >
                <h1 class="welcome" style="margin-bottom: 1px;">
                        <img class="img2" src="logo.png">
                        <a href="login2.php">
                                <img class="img3" src="logout.png" style="width:5%; padding-right: 1%">
                        </a>
                        SHAPESHIFT: WEIGHT <br>
                        <p class="mantra">SHIFT YOUR HEALTH, SHAPE YOUR FUTURE</p> 
                        <div align="right" style="font-size:20px; font-style:normal;padding-right: 40px;">
                                Logout
                        </div>
                </h1>
                <div class="navbar">
                        <a href="test.php">Home</a>
                        <div class="dropdown">
                                <button class="dropbtn"> Profile &#8609;
                                </button>
                                <div class="dropdown-content">
                                        <a href="profile.php">Edit profile</a>
                                        <a href="goals.php">Edit Goals</a>
					<a href="trend.php">7-day Trend</a>
					<a href="meals.php">Add Meals</a>
                                        <a href="workout.php">Add Workout</a>
                                        <a href="add_weight.php">Add Weight</a>
                                </div>
                        </div>
                        <div class="dropdown">
                                <button class="dropbtn"> Community &#8609;
                                </button>
                                <div class="dropdown-content">
                                        <a href="forum.php">Community Forum</a>
                                </div>
                        </div>
                        <a href="aboutus.php">About Us</a>
		</div>

		<div class="main" style="margin-left: 0px;width: 99%;">
                        <h2>ADD WEIGHT</h2>
                        <div class="card">
				<div class="card-body">
					<div style="font-size: 24px" id="currentWeight">
                        			<p><strong>Current Weight:</strong> <?php echo $currentWeight; ?> lbs</p>
			                </div>

					<form style="font-size: 24px" action="check7.php" method="post">
                        			<label for="new_weight">Add New Weight (lbs):</label>
                        			<input type="number" step="any" name="new_weight" id="new_weight" required></br></br>
                        			<button class="button1" type="submit">Update Weight</button>
                			</form>
				</div>
			</div>
		</div>
	</body>
</html>

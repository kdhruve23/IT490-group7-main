<?php
	session_start();
echo "Workouts data in session: ";
print_r($_SESSION['received_message']);
$jsonResponse = $_SESSION['received_message'];
$data = json_decode($jsonResponse, true);

// Access the "meals_data" part of the array
$mealsData = $data['meals_data'];

// Now $mealsData is an array containing meals information
// You can loop through it or access specific elements
foreach ($mealsData as $meal) {
    echo "Meal ID: " . $meal['meal_id'] . "<br>";
    echo "Meal Name: " . $meal['meal_name'] . "<br>";
    echo "Meal Datetime: " . $meal['meal_datetime'] . "<br>";
    // Add more fields as needed
}
echo '</br></br>';
// Check if there is an error parameter indicating no success
        if (isset($_GET['goal']) && $_GET['goal'] === 'success') {
                echo '<script>alert("Goal Updated!");</script>';
	}
	else if (isset($_GET['goal']) && $_GET['goal'] === 'no_success') {
                echo '<script>alert("Goal unable to update, please try again!");</script>';
	}
	else if (isset($_GET['workout']) && $_GET['workout'] === 'success') {
                echo '<script>alert("Workout Added!");</script>';
        }
        else if (isset($_GET['workout']) && $_GET['workout'] === 'no_success') {
                echo '<script>alert("Workout unable to add, please try again!");</script>';
	}
	else if (isset($_GET['weight']) && $_GET['weight'] === 'success') {
                echo '<script>alert("Weight Updated!");</script>';
        }
        else if (isset($_GET['weight']) && $_GET['weight'] === 'no_success') {
                echo '<script>alert("Weight unable to be updated, please try again!");</script>';
        }
	else if (isset($_GET['meal']) && $_GET['meal'] === 'success') {
                echo '<script>alert("Meal Added!");</script>';
	}
	else if (isset($_GET['meal']) && $_GET['meal'] === '_nosuccess') {
                echo '<script>alert("Meal could not be added, please try again!");</script>';
        }
	else if(isset($_GET['success']) && $_GET['success'] === 'true'){
		echo '<script>alert("Login Successful!");</script>';
	}

	if (isset($_SESSION['user_data'])) {
    		$user_data = $_SESSION['user_data'];
		$id = $user_data['user_id'];
		$email = $user_data['email'];
		$first = $user_data['first_name'];
		$last = $user_data['last_name'];
		$password = $user_data['password'];
		$movie = $user_data['movie'];
		$color = $user_data['color'];
		$goal = $user_data['goal'];
		$weight = $user_data['weight'];
 		echo "email: " . $email . " pass: " . $password . " goal: " . $goal . " weight: " . $weight . " name: " . $first . " " . $last . " movie: " . $movie . " color: " . $color . " id: " . $id;   	
	} else {
    		// Handle the case where user data is not available
    		echo "User data not available.";
	}

	// Check if workouts data is set in the session
if (isset($_SESSION['workouts'])) {
    $workout = $_SESSION['workouts'][0];
    $workout_name = $workout['workout_name'];
    echo "Workout Name: $workout_name";
} else {
    echo "Workouts data not available in the session.";
}

// Check if meals data is set in the session
if (isset($_SESSION['meals_data'])) {
    $meals_data = $_SESSION['meals_data'][0];
    $meal_name = $meals_data['meal_name'];
    echo " Meal Name: $meal_name";
} else {
    echo "Meals data not available in the session.";
}

	if (isset($_SESSION['user_totals']) && $_SESSION['user_totals'] !== 'None') {
    		$user_totals = $_SESSION['user_totals'];
		$calories = round($user_totals['total_calories'], 1);
		$protein = round($user_totals['total_protein'], 1);
		$carbs = round($user_totals['total_carbohydrates'], 1);
		$fat = round($user_totals['total_fat'], 1);
		$sugar = round($user_totals['total_sugar'], 1);
        	echo ", Calories: " . $calories . ", Protein: " . $protein . ", Carbs: " . $carbs . ", Fat: " . $fat . ", Sugar: " . $sugar  . "<br>";
    		
	} else {
    		// Handle the case where nutrition data is not available
    		echo "User totals not available.";
                //$nutrition_data = $_SESSION['nutrition_data'];
                $data_id = 0;
                $meal_id = 0;
                $calories = 0;
                $protein = 0;
                $carbs = 0;
                $fat = 0;
                $sugar = 0;
                $serving_size = 0;
                $servings = 0;
        }

	echo "email: " . $email . " name: " . $first . " " . $last . " movie: " . $movie . " color: " . $color . " id: " . $id;
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
                        WELCOME TO SHAPESHIFT <br>
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
                <p style="font-size:30px">
                        TODAY: <script> document.write(new Date().toDateString()); </script>
                </p>

		<div align="center">
		<a href="meals.php">
			<button class="button1">
                        	+ Add Meals
			</button>
		</a>
		<a href="goals.php">
		<button class="button1">
                        + Add Goal
		</button>
		</a>
		<a href="workout.php">
		<button class="button1">
                        + Add Workout
		</button>
		</a>
		<a href="add_weight.php">
		<button class="button1">
                        + Add Weight
		</button>
		</a>
		</div>
		<br><br>

		<div class="parent">
		<p style="font-size:20px; color:black"> Calories Intake: <?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ?> Protein Intake: <?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ?> Carbs Intake: <?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ?> Fat Intake: <?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ?> Sugar Intake: <?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ?></p>
			<div class="circle"> <?php echo $calories ?> / 1600 </div>
				<?php echo "&nbsp;&nbsp;" ?>
			<div class="circle"> <?php echo $protein ?> / 120 g  </div>
                                <?php echo "&nbsp;&nbsp;" ?>
			<div class="circle"> <?php echo $carbs ?> / 325 g </div>
                                <?php echo "&nbsp;&nbsp;" ?>
			<div class="circle"> <?php echo $fat ?> / 30 g </div>
				<?php echo "&nbsp;&nbsp;" ?>
			<div class="circle"> <?php echo $sugar ?> / 63 g </div>
                                <?php echo "&nbsp;&nbsp;" ?>
		</div>
		<br><br>
        </body>
</html>

<?php
        session_start();
	$user_data = $_SESSION['user_data'];
        $email = $user_data['email'];
        $user_id = $user_data['user_id'];
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
                        SHAPESHIFT: WORKOUTS <br>
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
		<div class="container">
			<div class="content">
			<h1>Exercise Tracker</h1>
		<form id="workoutForm" action="check6.php" method="post">
        		<h2>All Exercises</h2>
        		<!-- Display Exercises -->
        		<?php
        		// Fetch exercises from the API
        		//$exercises = json_decode(file_get_contents($apiUrl), true);
			$apiUrl = 'https://api.api-ninjas.com/v1/exercises';
			$apiKey = 'GpG1j0xD2Zjw5rN+zPlV3Q==b2pGItxJcN1RFSCH';

			$options = [
    				'http' => [
        			'header' => "X-Api-Key: $apiKey",
    				],
			];

			$context = stream_context_create($options);
			$result = file_get_contents($apiUrl, false, $context);
			$exercises = json_decode($result, true);

			if ($exercises === null || empty($exercises)) {
				echo 'Error fetching exercises from the API.';
			} else{
        		//echo '<ul>';
			foreach ($exercises as $exercise) {
    				echo '</br>';
    				echo '<div style="text-align: center; font-family: Arial, sans-serif;">';
   				echo '<strong style="font-size: 25px">Name:</strong> <span style="font-size: 25px;">' . $exercise['name'] . '</span><br>';
    				echo '<strong style="font-size: 25px">Type:</strong> <span style="font-size: 25px;">' . $exercise['type'] . '</span><br>';
    				echo '<strong style="font-size: 25px">Muscle:</strong> <span style="font-size: 25px;">' . $exercise['muscle'] . '</span><br>';
    				echo '<strong style="font-size: 25px">Equipment:</strong> <span style="font-size: 25px;">' . $exercise['equipment'] . '</span><br>';
    				echo '<button class="button1" name="addToWorkout" type="submit" value="' . $exercise['name'] . '">Add to Workout</button>';
    				echo '</br>';
    				echo '</div>';
			}
			}	
			?>
		</form>
		</div>
			<div class="sidebar">
                                <h2> Tracking workouts is crucial for: </h2>
				<ul style="font-size: 25px">
    				<li> Monitoring progress </li></br>
    				<li> Staying motivated </li></br>
    				<li> Identifying patterns and trends </li></br>
   	 			<li> Preventing plateaus </li> </br>
    				<li> Customizing workouts </li> </br>
    				<li> Adding accountability </li></br>
    				<li> Preventing injuries </li></br>
    				<li> Improving efficiency </li></br>
    				<li> Making data-driven decisions for optimal fitness results </li> </ul>
			</div>
		</div>
	</body>
</html>

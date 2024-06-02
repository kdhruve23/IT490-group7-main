<?php
        session_start();
        $user_data = $_SESSION['user_data'];
    //    echo "Workouts data in session: ";
//print_r($_SESSION['received_message']);
$jsonResponse = $_SESSION['received_message'];
$data = json_decode($jsonResponse, true);

// Access the "meals_data" part of the array
$mealsData = $data['meals_data'];

// Now $mealsData is an array containing meals information
// You can loop through it or access specific elements
foreach ($mealsData as $meal) {
//    echo "Meal ID: " . $meal['meal_id'] . "<br>";
//    echo "Meal Name: " . $meal['meal_name'] . "<br>";
//    echo "Meal Datetime: " . $meal['meal_datetime'] . "<br>";
    // Add more fields as needed
}
$workoutsData = $data['workouts'];

// Now $workoutsData is an array containing workout information
// You can loop through it or access specific elements
foreach ($workoutsData as $workout) {
//    echo "Workout ID: " . $workout['workout_id'] . "<br>";
//    echo "Workout Name: " . $workout['workout_name'] . "<br>";
//    echo "Created At: " . $workout['created_at'] . "<br>";
    // Add more fields as needed
}

// Access the "nutrition_data" part of the array
$nutritionData = $data['nutrition_data'];

// Now $nutritionData is an array containing nutrition information
// You can loop through it or access specific elements
foreach ($nutritionData as $nutrition) {
//    echo "Data ID: " . $nutrition['data_id'] . "<br>";
//    echo "Meal ID: " . $nutrition['meal_id'] . "<br>";
//    echo "Calories: " . $nutrition['calories'] . "<br>";
    // Add more fields as needed
}

	function displayMealsAndNutrition($mealsData, $nutritionData){
        	foreach ($mealsData as $meal) {
			echo "<tr>";
			$formattedDate = date('Y-m-d', strtotime($meal['meal_datetime']));
                	echo "<td>{$formattedDate}</td>";
                	echo "<td>{$meal['meal_name']}</td>";
                
                	$nutrition = findCorrespondingNutrition($nutritionData, $meal['meal_id']);
                	if ($nutrition) {
                    		echo "<td>{$nutrition['calories']}</td>";
				echo "<td>{$nutrition['protein']}</td>";
				echo "<td>{$nutrition['carbohydrates']}</td>";
				echo "<td>{$nutrition['fat']}</td>";
				echo "<td>{$nutrition['sugar']}</td>";
                	} else {
                    		// Handle the case where nutrition data is not found
                    		echo "<td colspan='2'>No nutrition data</td>";
                	}
                	echo "</tr>";
        	}
	}
	function findCorrespondingNutrition($nutritionData, $mealId){
        	foreach ($nutritionData as $nutrition) {
            		if ($nutrition['meal_id'] === $mealId) {
                		return $nutrition;
            		}
        	}
        	return null;
    	}

    	// Function to display workouts
    	function displayWorkouts($workoutData){
        	foreach ($workoutData as $workout) {		
			echo "<tr>";
			$formattedDate = date('Y-m-d', strtotime($workout['created_at']));
                	echo "<td>{$formattedDate}</td>";
                	echo "<td>{$workout[2]}</td>";
                	echo "</tr>";
        	}
	}
?>
<script>
function redirectToCheck10() {
    // Use window.location.href to redirect to the desired page
    window.location.href = 'trend.php';  //normally go to check9.php
}
</script>
<html>
        <head>
                <title> ShapeShift </title>
		<link rel="stylesheet" href="/index.css" />
		
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            color: #008080;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #008080;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #008080;
            color: white;
        }
    </style>
</table>
        </head>

        <body style="color:#008080;" >
                <h1 class="welcome" style="margin-bottom: 1px;">
                        <img class="img2" src="logo.png">
                        <a href="login2.php">
                                <img class="img3" src="logout.png" style="width:5%; padding-right: 1%">
                        </a>
                        SHAPESHIFT: 7-DAY TREND <br>
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
		<button class="button1" id="toggleButton" onclick="redirectToCheck10()">Show Previous 7 Days</button>
		<h2>Workouts</h2>
		<table>
    			<tr>
        			<th>Date</th>
        			<th>Workout Name</th>
			</tr>
			<?php displayWorkouts($workoutsData); ?>
		</table>

		<h2>Meals</h2>
		<table>
    			<tr>
        			<th>Date</th>
        			<th>Meal Name</th>
        			<th>Calories Consumed</th>
        			<th>Protein</th>
        			<th>Carbs</th>
				<th>Fat</th>
				<th> Sugar </th>
			</tr>
			    <?php displayMealsAndNutrition($mealsData, $nutritionData); ?>

		</table>
        </body>
</html>

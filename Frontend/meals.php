<?php
session_start();
$user_data = $_SESSION['user_data'];
$email = $user_data['email'];
$user_id = $user_data['user_id'];
echo "Email: " . $email . "<br>";
echo "User ID: " . $user_id . "<br>";
if (isset($_SESSION['user_data'])) {
                $user_data = $_SESSION['user_data'];
                $id = $user_data['user_id'];
                $email = $user_data['email'];
                $first = $user_data['first_name'];
                $last = $user_data['last_name'];
                $password = $user_data['password'];
                $movie = $user_data['movie'];
                $color = $user_data['color'];

        } else {
                // Handle the case where user data is not available
                echo "User data not available.";
        }
	if (isset($_SESSION['user_totals']) && $_SESSION['user_totals'] !== 'None') {
                $user_totals = $_SESSION['user_totals'];
                // Assuming there can be multiple meals/nutrition data
                //$data_id = $user_totals['data_id'];
                //$meal_id = $user_totals['meal_id'];
                $calories = $user_totals['total_calories'];
                $protein = $user_totals['total_protein'];
                $carbs = $user_totals['total_carbohydrates'];
                $fat = $user_totals['total_fat'];
                $sugar = $user_totals['total_sugar'];
                //$serving_size = $user_totals['serving_size'];
                //$servings = $user_totals['servings'];

                // Display or use the meal/nutrition data as needed
                echo "Meal ID: " . $meal_id . ", Calories: " . $calories . ", Protein: " . $protein . ", Carbs: " . $carbs . ", Fat: " . $fat . ", Sugar: " . $sugar . ", Serving Size: " . $serving_size . ", Servings: " . $servings . "<br>";

        } else {
                // Handle the case where nutrition data is not available
                echo "User totlas not available.";
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

        echo "email: " . $email . " pass: " . $password . " name: " . $first . " " . $last . " movie: " . $movie . " color: " . $color . " id: " . $id;
        echo "cal: " . $calories;
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
                        SHAPESHIFT: LOG FOOD <br>
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

                <div class="main" style="margin-left: 0px;width: 99%;">
                        <h2>ADD MEALS</h2>
                        <div class="card">
                                <div class="card-body">
                                        <form style="font-size:24px" id="nutritionForm" action="check4.php" method="post">
                                                <label for="foodQuery">Enter a Food Item:</label>
                                                <input style="font-size: 20px" type="text" id="foodQuery" name="foodQuery" required>
                                                </br> </br>
                                                <!-- Add servings input and buttons -->
                                                <div style="width: 1000px;">
                                                        <label for="servings">Number of Servings:</label>
                                                        <input style="font-size: 20px" type="number" id="servings" name="servings" value="1" min="1">

                                                        <button class="button1" type="button" onclick="adjustServings(1)">Increase Servings</button>
                                                        <button class="button1" type="button" onclick="adjustServings(-1)">Decrease Servings</button>
                                                </div>
                                                </br></br>
                                                <button class="button1" type="button" onclick="getNutritionData()">Get Nutrition Data</button>
                                        </form>

                                        <div id="result"></div>
                                        <button class="button1" type="button" id="addMealButton" style="display: none;" onclick="sendDataToCheck4PHP()">Add Meal </button>
                                        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

                                                <!-- ... Your HTML code ... -->
<script>
    function getNutritionData() {
        // Get the user input
        var foodQuery = document.getElementById("foodQuery").value;

        // Make a request to the Ninja Nutrition API
        $.ajax({
            method: 'GET',
            url: 'https://api.api-ninjas.com/v1/nutrition?query=' + encodeURIComponent(foodQuery),
            headers: { 'X-Api-Key': 'GpG1j0xD2Zjw5rN+zPlV3Q==b2pGItxJcN1RFSCH' },
            contentType: 'application/json',
            success: function (result) {
                // Display the nutrition data
                displayNutritionData(result);
            },
            error: function ajaxError(jqXHR) {
                console.error('Error: ', jqXHR.responseText);
                displayError('Error fetching nutrition data. Please try again.');
            }
        });
    }
    var adjustedCalories, adjustedProtein, adjustedCarbohydrates, adjustedFat, adjustedSugar, name, servingSize, servings;
    function displayNutritionData(data) {
        var resultContainer = document.getElementById("result");

        // Clear previous results
        resultContainer.innerHTML = "";

        if (data.length > 0) {
            // Assuming there is only one result in the array, you can access it directly
            var foodData = data[0];

            // Extract nutrition values
            name = foodData.name;
            var calories = foodData.calories;
            var protein = foodData.protein_g;
            var carbohydrates = foodData.carbohydrates_total_g;
            var fat = foodData.fat_total_g;
            var sugar = foodData.sugar_g;
            servingSize = foodData.serving_size_g;

            // Get the number of servings from the input field
            servings = parseInt(document.getElementById("servings").value, 10);

            // Calculate adjusted nutrition values based on the number of servings
            adjustedCalories = calories * servings;
            adjustedProtein = protein * servings;
            adjustedCarbohydrates = carbohydrates * servings;
            adjustedFat = fat * servings;
            adjustedSugar = sugar * servings;
            // Display the adjusted nutrition data
            resultContainer.innerHTML = `
                    <h2>${name}</h2>
                    <p style="font-size: 24px">Calories: ${adjustedCalories} kcal</p>
                    <p style="font-size: 24px">Protein: ${adjustedProtein} g</p>
                    <p style="font-size: 24px">Carbohydrates: ${adjustedCarbohydrates} g</p>
                    <p style="font-size: 24px">Fat: ${adjustedFat} g</p>
                    <p style="font-size: 24px">Sugar: ${adjustedSugar} g</p>
                    <p style="font-size: 24px">Serving Size: ${servingSize} g</p>
                    <p style="font-size: 24px">Number of Servings: <span id="displayedServings">${servings}</span></p>
                `;

            document.getElementById("addMealButton").style.display = "block";
        } else {
            // Display an error message if no results are found
            displayError('Food item not found. Please try another.');
        }
    }

    function sendDataToCheck4PHP() {
        // Get the adjusted nutrition values
        var form = document.getElementById("nutritionForm");

        // Add hidden input fields to the form
        var hiddenFields = [
            { name: 'mealName', value: name },
            { name: 'mealCalories', value: adjustedCalories },
            { name: 'mealProtein', value: adjustedProtein },
            { name: 'mealCarbohydrates', value: adjustedCarbohydrates },
            { name: 'mealFat', value: adjustedFat },
            { name: 'mealSugar', value: adjustedSugar },
            { name: 'mealServingSize', value: servingSize },
	    { name: 'mealServings', value: servings },
	    { name: 'mealDateTime', value: getCurrentDateTime() }, // Add this line to set the mealDateTime
        ];

        hiddenFields.forEach(function (field) {
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = field.name;
            input.value = field.value;
            form.appendChild(input);
        });

        // Submit the form
        form.submit();
    }
    function getCurrentDateTime() {
    	var now = new Date();
    	var year = now.getFullYear();
    	var month = (now.getMonth() + 1).toString().padStart(2, '0');
    	var day = now.getDate().toString().padStart(2, '0');
    	var hours = now.getHours().toString().padStart(2, '0');
    	var minutes = now.getMinutes().toString().padStart(2, '0');
    	var seconds = now.getSeconds().toString().padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`;
    }
    function adjustServings(change) {
        // Adjust the number of servings based on the change (1 for increase, -1 for decrease)
        var servingsInput = document.getElementById("servings");
        var currentServings = parseInt(servingsInput.value, 10);
        var newServings = Math.max(1, currentServings + change); // Ensure the minimum number of servings is 1
        servingsInput.value = newServings;

        // Update the displayed number of servings
        var displayedServings = document.getElementById("displayedServings");
        if (displayedServings) {
            displayedServings.textContent = newServings;
        }
    }

    function displayError(message) {
        var resultContainer = document.getElementById("result");
        resultContainer.innerHTML = `<p style="color: red;">${message}</p>`;
    }
</script>
                                </div>
                        </div>
                </div>
        </body>
</html>

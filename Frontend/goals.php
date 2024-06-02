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
            SHAPESHIFT: GOALS <br>
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
            <h2>CHOOSE YOUR FITNESS GOAL</h2>
            <div class="card">
                <div class="card-body">
                    <form style="font-size: 24px" id="fitnessForm" action="check5.php" method="post">
                        <label>
                            <input type="radio" name="fitness_goal" value="lose_weight" onclick="showGoalInfo('lose_weight')" required>
                            Lose Weight
                        </label>
                        <label>
                            <input type="radio" name="fitness_goal" value="build_muscle" onclick="showGoalInfo('build_muscle')" required>
                            Build Muscle
                        </label>
                        <label>
                            <input type="radio" name="fitness_goal" value="improve_endurance" onclick="showGoalInfo('improve_endurance')" required>
                            Improve Endurance
                        </label>
                        <label>
                            <input type="radio" name="fitness_goal" value="maintain_health" onclick="showGoalInfo('maintain_health')" required>
                            Maintain Health
                        </label>
                        </br>
                        <input type="hidden" id="selectedGoal" name="selectedGoal" value="">
                        <button class="button1" type="submit">Submit</button>
                    </form>
                    <div id="goalDescription"></div>
                </div>
            </div>
        </div>
        <script>
            function showGoalInfo(goal) {
                var goalDescriptions = {
                    'lose_weight': {
                        'description': 'Losing weight involves creating a calorie deficit by consuming fewer calories than you burn. Focus on a balanced diet and incorporate regular exercise, including both cardio and strength training.',
                        'recommendation': 'Consider consulting with a nutritionist or a fitness professional to create a personalized plan.'
                    },
                    'build_muscle': {
                        'description': 'Building muscle requires a combination of strength training exercises and a protein-rich diet. Focus on compound exercises targeting major muscle groups.',
                        'recommendation': 'Ensure you get adequate protein intake, and consider a workout routine that includes both resistance training and sufficient rest.'
                    },
                    'improve_endurance': {
                        'description': 'Improving endurance involves cardiovascular exercises such as running, cycling, or swimming. Gradually increase the duration and intensity of your workouts.',
                        'recommendation': 'Include a mix of high-intensity interval training (HIIT) and steady-state cardio in your routine.'
                    },
                    'maintain_health': {
                        'description': 'Maintaining health involves a balanced lifestyle with a mix of cardio, strength training, and flexibility exercises. Focus on a varied and nutritious diet.',
                        'recommendation': 'Prioritize overall well-being, including regular check-ups and stress management.'
                    }
                };

                var descriptionDiv = document.getElementById('goalDescription');
                var description = goalDescriptions[goal]['description'];
                var recommendation = goalDescriptions[goal]['recommendation'];

                descriptionDiv.innerHTML = '<p style="font-size: 30px"><strong>Description:</strong> ' + description + '</p>' + '<p style="font-size: 30px"><strong>Recommendation:</strong> ' + recommendation + '</p>';
                
                // Set the selected goal value to the hidden input field
                document.getElementById('selectedGoal').value = goal;
            }
        </script>
    </body>
</html>

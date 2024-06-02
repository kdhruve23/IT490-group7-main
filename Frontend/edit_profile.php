<?php
        session_start();
        $user_data = $_SESSION['user_data'];
        $email = $user_data['email'];
        $height = $user_data['height'];
        $weight = $user_data['weight'];
        $first = $user_data['first_name'];
        $last = $user_data['last_name'];
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
                        SHAPESHIFT: EDIT PROFILE <br>
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
                        <h2>EDIT PROFILE</h2>
                        <div class="card">
                                <div class="card-body">
                                        <form method="post" action="check8.php">
                                                <label for="email">Email Address:</label>
                                                <input type="text" id="email" name="email" value="<?php echo $email ?>" readonly>
                                                </br></br>

                                                <label for="firstName">First Name:</label>
                                                <input type="text" id="firstName" name="firstName" value="<?php echo $first ?>">
                                                </br></br>

                                                <label for="lastName">Last Name:</label>
                                                <input type="text" id="lastName" name="lastName" value="<?php echo $last ?>">
                                                </br></br>

                                                <label for="height">Height:</label>
                                                <input type="text" id="height" name="height" value="<?php echo $height ?>"> in
                                                </br></br>

                                                <label for="weight">Weight:</label>
                                                <input type="text" id="weight" name="weight" value="<?php echo $weight ?>"> lbs
                                                </br></br>

                                                <button class="button1" type="submit">Save Changes</button>
                                        </form>
                                </div>
                        </div>
                </div>
        </body>
</html>

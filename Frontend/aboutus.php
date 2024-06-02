<?php
        session_start();
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['password'] = $_POST['password'];
        $email = $_SESSION['email'];
        //include 'write2.php';
?>
<html>
        <head>
                <title> ShapeShift </title>
                <link rel="stylesheet" href="/index.css" />
        <style>
            .content-container {
                background-color: #fff; /* Set the background color for the content container */
                padding: 20px; /* Adjust padding as needed */
                text-align: center;
            }
            .img4-container {
                text-align: center;
                margin-top: 40px; /* Adjust the top margin to add more space */
            }

            .img4 {
                display: block;
                margin: auto;
                max-width: 40%;
                height: auto;
                border: 2px solid teal;
            }

            .creators-description {
                text-align: center;
                margin-top: 20px;
                max-width: 70%;
                margin-left: auto;
                margin-right: auto;
                font-size: 22px;
            }
        </style>
        </head>

        <body style="color:#008080;" >
                <h1 class="welcome" style="margin-bottom: 1px;">
                        <img class="img2" src="logo.png">
                        <a href="login2.php">
                                <img class="img3" src="logout.png" style="width:5%; padding-right: 1%">
                        </a>
                        SHAPESHIFT: ABOUT US <br>
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
                                        <a href="edit_profile.php">Edit profile</a>
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
                <div class="content-container">
                <h1 style="text-align: center; margin-top: 50px;">About the Creators</h1>
                <div class="img4-container">
                        <img class="img4" src="us.JPG" alt="Image of the Founders">
                </div>

                 <p class="creators-description">
                 Meet the masterminds behind the Shapeshift Application! From left to right, first we have our frontend developer Keya, her favorite workout is math; a good brain exercise! Up next we got our champion weightlifter, Ritik, who is also our database manager. In the middle we have Adam, our backend developer, whose favorite sport is to run after his child. And finally our rabbitmq manager, Nikita, who loves smores and conducted the smores trip in the middle of class. We're all excited for our users to integrate the application into their daily lives and use it to ShapeShift into their best selves!
                 </p>
                </div>
        </body>
</html>

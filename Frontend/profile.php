<?php
        session_start();
        $user_data = $_SESSION['user_data'];
        $email = $user_data['email'];
	$height = $user_data['height'];
	$weight = $user_data['weight'];
	$first = $user_data['first_name'];
	$last = $user_data['last_name'];
	
	if (isset($_GET['profile']) && $_GET['profile'] === 'success') {
                echo '<script>alert("Profile Updated!");</script>';
        }
        else if (isset($_GET['profile']) && $_GET['profile'] === 'no_success') {
                echo '<script>alert("Profile unable to update, please try again!");</script>';
        }

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
                        SHAPESHIFT: PROFILE <br>
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
        		<h2>PROFILE</h2>
        		<div class="card">
            		<div class="card-body">
               	 	<i class="fa fa-pen fa-xs edit"></i>
                	<table>
                    		<tbody>
                        		<tr>
                            			<td style="width: 150px;">Name</td>
                            			<td>:</td>
						<td><?php echo $first . " " .  $last ?></td>
                        		</tr>
                        		<tr>
                            			<td>Email Address</td>
                            			<td>:</td>
						<td><?php echo $email ?> </td>
                       	 		</tr>
                        		<tr>
                            			<td>Height</td>
                           		 	<td>:</td>
						<td><?php echo $height ?> in</td>
                        		</tr>
                        		<tr>
						<td>Weight</td>
						<td>:</td>
						<td><?php echo $weight ?> lbs</td>
                            		</tr>
                    		</tbody>
			</table>
			<button class="button1" onclick="editProfile()">Edit Profile</button>
            		</div>
			</div>
		</div>
		<script>
        		function editProfile() {
            			// Redirect to the page where users can edit their profile
            			window.location.href = 'edit_profile.php';
        		}
    		</script>
        </body>
</html>

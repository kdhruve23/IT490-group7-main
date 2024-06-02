<?php
        session_start();
	$user_data = $_SESSION['user_data'];
	$email = $user_data['email'];
	$first_name = $user_data['first_name'];
	$jsonResponse = $_SESSION['received_message'];
	//print_r($jsonResponse);
	$data = json_decode($jsonResponse, true);
	$posts = $data['all_posts'];
	//print_r($posts);

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
                        SHAPESHIFT: COMMUNITY FORUM <br>
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
        			<h1>Nutrition Social</h1>
        			<!-- Post Form -->
        			<form action="check9.php" method="post">
            				<textarea name="postContent" placeholder="What's on your mind?"></textarea>
            				<button class="button1" type="submit">Post</button>
        			</form>
				<!-- Display Posts -->
				<?php
					foreach ($posts as $post){
    						echo '<div class="post">';
	    					echo '<strong>' . $user_data['first_name'] . '</strong>';
    						echo '<p>' ;
    						echo $post['post_content'] . '</p>';
    						echo '</div>';
					} 
				?>

				<?php
				$posts = [
    					['user' => 'John', 'content' => 'Enjoying a healthy salad today!'],
    					['user' => 'Alice', 'content' => 'Just finished a great workout session.'],
				];

				foreach ($posts as $post) {
                			echo '<div class="post">';
                			echo '<strong>' . $post['user'] . '</strong>';
                			echo '<p>' . $post['content'] . '</p>';
                			echo '</div>';
            			}
				?>
			</div>

			<div class="sidebar" style="font-size: 20px;">
				<b><p style="font-size: 24px"> Engaging with others on Shapeshift is beneficial for: </p></b>
				
				<b> Motivation: </b>
        			Shared fitness journeys inspire commitment and motivation.
				</br>
    				 <b> Learning: </b>
        			 Exchanging experiences offers valuable insights into workouts and nutrition.
				</br>
				<b> Accountability: </b>
        			Community support fosters goal-setting and accountability.
				</br>
    				<b> Knowledge Exchange: </b>
        			Sharing tips and advice enhances overall well-being.
				</br>
    				<b> Celebrating Achievements: </b>
        			Acknowledging successes creates a positive and supportive atmosphere.
				</br>
    				<b> Social Connection: </b>
        			Reduces feelings of isolation and provides a sense of belonging.
				</br>
    				<b> Problem Solving: </b>
        			Community helps overcome challenges through shared experiences.
				</br>
    				<b> Diverse Perspectives: </b>
        			Varied backgrounds bring diverse insights and approaches.
			
			</div>
		</div>
	</body>
</html>

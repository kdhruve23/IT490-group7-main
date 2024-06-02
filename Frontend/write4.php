<?php
session_start();
require 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
//$email = $_SESSION['email'];
//$user_id = $_SESSION['user_id'];
$user_data = $_SESSION['user_data'];
$email = $user_data['email'];
$user_id = $user_data['user_id'];

// Access other posted data
$name = $_POST['mealName'];
$calories = $_POST['mealCalories'];
$protein = $_POST['mealProtein'];
$carbohydrates = $_POST['mealCarbohydrates'];
$fat = $_POST['mealFat'];
$sugar = $_POST['mealSugar'];
$servingSize = $_POST['mealServingSize'];
$servings = $_POST['mealServings'];
$mealDateTime = $_POST['mealDateTime'];

echo "WRITE4.PHP==> Name: " . $name . "<br>";
echo "Calories: " . $calories . "<br>";
echo "Protein: " . $protein . "<br>";
echo "Carbohydrates: " . $carbohydrates . "<br>";
echo "Fat: " . $fat . "<br>";
echo "Sugar: " . $sugar . "<br>";
echo "Serving Size: " . $servingSize . "<br>";
echo "Servings: " . $servings . "<br>";
echo "Email: " . $email . "<br>";
echo "user id: " . $user_id . "<br>";
echo "datetime: " . $mealDateTime;

// Ensure that the request is a POST request
if (isset($email)) {
//if ($name != '')) {
    // Process the data as needed (e.g., store in a database)

    // Send a response back to the client
    echo 'Success2'; // You can modify this based on the result of your processing

//if (isset($_SESSION['email']) && isset($_SESSION['password']) && isset($_SESSION['first']) && isset($_SESSION['last']) && isset($_SESSION['height']) && isset($_SESSION['weight'])) {
    $rabbitMQHosts = ['10.147.17.34', '10.147.17.79', '10.147.17.44'];
    $rabbitMQPort = 5672;
    $rabbitMQUser = 'backend';
    $rabbitMQPassword = 'password';
    $virtualHost = '/';
    $exchangeName = 'frontend-backend'; // Replace with your exchange name
    try {
        // Create a connection to RabbitMQ
        foreach ($rabbitMQHosts as $rabbitMQHost) {
                try {
                        // Create a connection to RabbitMQ
                        $connection = new AMQPStreamConnection($rabbitMQHost, $rabbitMQPort, $rabbitMQUser, $rabbitMQPassword, $virtualHost);

                        // Connection successful, break out of the loop
                        break;
                } catch (\Exception $e) {
                        // Connection failed, try the next IP address
                        echo "Failed to connect to RabbitMQ at $rabbitMQHost: " . $e->getMessage() . "\n";
                }
        }
        // Create a channel
        $channel = $connection->channel();

        // Declare the exchange (direct type)
        $channel->exchange_declare($exchangeName, 'direct', false, true, false);

        // Create a message
        $messageBody = json_encode([
            'email' => $email,
            'meal_name' => $name,
            'calories' => $calories,
	    'protein' => $protein,
	    'carbohydrates' => $carbohydrates,
	    'fat' => $fat,
	    'sugar' => $sugar,
	    'servingSize' => $servingSize,
	    'servings' => $servings,
	    'user_id' => $user_id,
	    'mealDateTime' => $mealDateTime
        ]);

        // Specify the routing key for the backend queue
        $routingKey = 'meals.request';

        // Publish the message to the exchange with the routing key
        $message = new AMQPMessage($messageBody);
        $channel->basic_publish($message, $exchangeName, $routingKey);

        // Close the channel and the connection
        $channel->close();
        $connection->close();

        // Clear the session variables
        unset($_SESSION['email']);
        unset($_SESSION['name']);
        unset($_SESSION['calories']);
        unset($_SESSION['protein']);
        unset($_SESSION['carbohydrates']);
	unset($_SESSION['fat']);
	unset($_SESSION['sugar']);
        unset($_SESSION['servingSize']);
        unset($_SESSION['servings']);

        echo "Message sent to the exchange with routing key '$routingKey': $messageBody\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "User Not Found.";
}
?>

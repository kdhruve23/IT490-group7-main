<?php
session_start();
$name = $_POST['mealName'];
$calories = $_POST['mealCalories'];
$protein = $_POST['mealProtein'];
$carbohydrates = $_POST['mealCarbohydrates'];
$fat = $_POST['mealFat'];
$sugar = $_POST['mealSugar'];
$servingSize = $_POST['mealServingSize'];
$servings = $_POST['mealServings'];
$mealDateTime = $_POST['mealDateTime'];
$user_data = $_SESSION['user_data'];
$email = $user_data['email'];
$user_id = $user_data['user_id'];

echo "Name: " . $name . "<br>";
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
include 'write4.php';

require 'vendor/autoload.php'; // Include the Composer autoloader

use PhpAmqpLib\Connection\AMQPStreamConnection;
session_start();

// RabbitMQ server connection parameters
$rabbitMQHosts = ['10.147.17.34', '10.147.17.79', '10.147.17.44'];
$rabbitMQPort = 5672; // Default RabbitMQ port
$rabbitMQUser = 'backend';
$rabbitMQPassword = 'password';
$virtualHost = '/';

// Queue name (frontend-to-backend-queue)
$queueName = 'front-meals-response'; // Replace with the actual queue name

try {
    // Create a connection to RabbitMQ
    $connected = false;
    foreach ($rabbitMQHosts as $rabbitMQHost) {
        try {
            // Create a connection to RabbitMQ
            $connection = new AMQPStreamConnection($rabbitMQHost, $rabbitMQPort, $rabbitMQUser, $rabbitMQPassword, $virtualHost);

            // Connection successful, break out of the loop
            $connected = true;
            break;
        } catch (\Exception $e) {
            // Connection failed, try the next IP address
            //echo "Failed to connect to RabbitMQ at $rabbitMQHost: " . $e->getMessage() . "\n";
        }
    }

    if (!$connected) {
        echo "Failed to connect to RabbitMQ. Check your connection settings.\n";
        exit();
    }

    // Create a channel
    $channel = $connection->channel();

    // Declare the queue
    list($queueName) = $channel->queue_declare($queueName, false, true, false, false);

    //echo "Waiting for messages from the backend...\n";

    // Set a timeout of 30 seconds (adjust as needed)
    $timeout = 40;
    $start_time = time();
    ob_start();

    $callback = function ($message) {
        // Store the received message in the session
        $_SESSION['received_message'] = $message->body;

        echo 'Received: ', $message->body, "\n";
        var_dump($message->body);
        $decodedMessage = json_decode($message->body, true);
        if ($decodedMessage !== null) {
        // Check if the 'status' field is 'success'
                if (isset($decodedMessage['status']) && $decodedMessage['status'] === 'success') {
                        // Store user data in the session
                        $_SESSION['user_totals'] = $decodedMessage['user_totals'];
                        $_SESSION['user_data'] = $decodedMessage['user_data'];
                }

                // Send a JSON response back to the AJAX request
               // header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Meal added successfully']);
		header("Location: test.php?meal=success");
		exit();
        } else {
                // Handle JSON decoding error (invalid JSON format)
                if (isset($decodedMessage['status']) && $decodedMessage['status'] === 'success') {
                        // Store user data in the session
                        $_SESSION['user_totals'] = $decodedMessage['user_totals'];
                        $_SESSION['user_data'] = $decodedMessage['user_data'];
                }

                //header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format']);
		header("Location: test.php?meal=no_success");
		exit();
        }
    };


    // Consume messages from the queue
    $channel->basic_consume($queueName, '', false, true, false, false, $callback);

    // Wait for messages or until the timeout is reached
    while (count($channel->callbacks) && (time() - $start_time) < $timeout) {
        $channel->wait(null, false, $timeout);
    }

    $channel->close();
    $connection->close();

    // Check if the timeout occurred
    if ((time() - $start_time) >= $timeout) {
        echo "Timeout: No messages received after $timeout seconds.\n";
        // You can add additional logic here if needed
    }
    ob_end_flush();

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

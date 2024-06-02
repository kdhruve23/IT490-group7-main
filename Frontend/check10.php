<?php
session_start();
$user_data = $_SESSION['user_data'];
$email = $user_data['email'];

//write10.php
require 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
echo $email;

// Ensure that the request is a POST request
if (isset($email)) {
    echo 'Success'; // You can modify this based on the result of your processing
    $rabbitMQHosts = ['10.147.17.34', '10.147.17.79', '10.147.17.44'];
    $rabbitMQPort = 5672;
    $rabbitMQUser = 'backend';
    $rabbitMQPassword = 'password';
    $virtualHost = '/';
    $exchangeName = 'frontend-backend'; // Replace with your exchange name
    echo "email: " . $email;
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
        ]);

        // Specify the routing key for the backend queue
        $routingKey = 'trend.request';

        // Publish the message to the exchange with the routing key
        $message = new AMQPMessage($messageBody);
        $channel->basic_publish($message, $exchangeName, $routingKey);

        // Close the channel and the connection
        $channel->close();
        $connection->close();

        // Clear the session variables
        unset($_SESSION['email']);

        echo "Message sent to the exchange with routing key '$routingKey': $messageBody\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Email not found";
}

//read6.php
$queueName = 'front-trend-response';
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
    echo "reading";
    // Create a channel
    $channel = $connection->channel();

    // Declare the queue
    list($queueName) = $channel->queue_declare($queueName, false, true, false, false);

    echo "Waiting for messages from the backend...\n";

    // Set a timeout of 30 seconds (adjust as needed)
    $timeout = 40;
    $start_time = time();

    // Callback function to process received messages
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
                        $_SESSION['workout_data'] = $decodedMessage['workout_data'];
			$_SESSION['user_data'] = $decodedMessage['user_data'];
			$_SESSION['meal_data'] = $decodedMessage['meal_data'];
                        $_SESSION['nutrition_data'] = $decodedMessage['nutrition_data'];
                }

                header("Location: trend.php?post=success");
                exit();
        } else {
                // Handle JSON decoding error (invalid JSON format)
                if (isset($decodedMessage['status']) && $decodedMessage['status'] === 'success') {
                        // Store user data in the session
			$_SESSION['workout_data'] = $decodedMessage['workout_data'];
                        $_SESSION['user_data'] = $decodedMessage['user_data'];
                        $_SESSION['meal_data'] = $decodedMessage['meal_data'];
                        $_SESSION['nutrition_data'] = $decodedMessage['nutrition_data'];
		}
                header("Location: trend.php?post=no_success");
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

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

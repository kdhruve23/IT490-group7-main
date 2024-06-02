<?php

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
$queueName = 'front-reg-response'; //back-front

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

    // Declare the queue
    list($queueName) = $channel->queue_declare($queueName, false, true, false, false);

    echo "Waiting for messages from the backend...\n";

    // Set a timeout of 30 seconds (adjust as needed)
    $timeout = 100;
    $start_time = time();
    ob_start(); 

    // Callback function to process received messages
    $callback = function ($message) {
        // Store the received message in the session
        $_SESSION['received_message'] = $message->body;

        echo 'Received: ', $message->body, "\n";

	$decodedMessage = json_decode($message->body, true);

        if ($decodedMessage !== null) {
        // Check if the 'status' field is 'success'
        	if (isset($decodedMessage['status']) && $decodedMessage['status'] === 'success') {
            		// Redirect the user to the home screen or any desired location
            		header("Location: login2.php?success=true");
            		exit(); // Ensure script termination after the header redirection
		} else if (isset($decodedMessage['status']) && $decodedMessage['status'] === 'success') {
                        // Redirect the user to the home screen or any desired location
                        header("Location: index.php?error=user_exists");
			exit(); // Ensure script termination after the header redirection
		} 
		else {
            		header("Location: index.php?error=no_success");
            		exit();
        	}
    	} else {
        	// Handle JSON decoding error (invalid JSON format)
        	header("Location: index.php?error=json_decode_error");
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
        ob_end_clean(); // Clean (erase) the output buffer
        header("Location: index.php?error=timeout");
        exit(); // Terminate the script
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>

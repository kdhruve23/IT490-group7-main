<?php
session_start();
require 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if (isset($_SESSION['email']) && isset($_SESSION['password'])) {
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];

    //require 'vendor/autoload.php';
    //use PhpAmqpLib\Connection\AMQPStreamConnection;
    //use PhpAmqpLib\Message\AMQPMessage;

    $rabbitMQHosts = ['10.147.17.34', '10.147.17.79', '10.147.17.44'];
    $rabbitMQPort = 5672;
    $rabbitMQUser = 'backend';
    $rabbitMQPassword = 'password';
    $virtualHost = '/';
    $exchangeName = 'frontend-backend'; // Replace with your exchange name
    echo $email . " " . $password;
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
		echo "hhh: " . $rabbitMQHost;
        }

        // Create a channel
        $channel = $connection->channel();

        // Declare the exchange (direct type)
        $channel->exchange_declare($exchangeName, 'direct', false, true, false);

        // Create a message
        $messageBody = json_encode([
            'email' => $email,
            'password' => $password
        ]);

        // Specify the routing key for the backend queue
        $routingKey = 'log.request'; //login

        // Publish the message to the exchange with the routing key
        $message = new AMQPMessage($messageBody);
        $channel->basic_publish($message, $exchangeName, $routingKey);

        // Close the channel and the connection
        $channel->close();
        $connection->close();

        // Clear the session variables
        unset($_SESSION['email']);
        unset($_SESSION['password']);

        echo "Message sent to the exchange with routing key '$routingKey': $messageBody\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Email and password not found in the session.";
}
?>

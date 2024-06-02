<?php
session_start();
require 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if (isset($_SESSION['email']) && isset($_SESSION['password']) && isset($_SESSION['first']) && isset($_SESSION['last']) && isset($_SESSION['height']) && isset($_SESSION['weight']) && isset($_SESSION['movie']) && isset($_SESSION['color'])) {
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];
    $first = $_SESSION['first'];
    $last = $_SESSION['last'];
    $height = $_SESSION['height'];
    $weight = $_SESSION['weight'];
    $color = $_SESSION['color'];
    $movie = $_SESSION['movie'];

    $rabbitMQHosts = ['10.147.17.34', '10.147.17.79', '10.147.17.44'];
    $rabbitMQPort = 5672;
    $rabbitMQUser = 'backend';
    $rabbitMQPassword = 'password';
    $virtualHost = '/';
    $exchangeName = 'frontend-backend';

    echo "name: " .$first . " " . $last . " email: " . $email . " pass: " . $password . " height: " . $height . " weight: " . $weight;

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
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'password' => $password,
            'height' => $height,
            'weight' => $weight,
            'movie' => $movie,
	    'color' => $color,
	    'goal' => 'null',
        ]);

        // Specify the routing key for the backend queue
        $routingKey = 'reg.request'; // frontend

        // Publish the message to the exchange with the routing key
        $message = new AMQPMessage($messageBody);
        $channel->basic_publish($message, $exchangeName, $routingKey);

        // Close the channel and the connection
        $channel->close();
        $connection->close();

        // Clear the session variables
        unset($_SESSION['email']);
        unset($_SESSION['password']);
        unset($_SESSION['first']);
        unset($_SESSION['last']);
        unset($_SESSION['height']);
        unset($_SESSION['weight']);

        echo "Message sent to the exchange with routing key '$routingKey': $messageBody\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Registration did not succeed.";
}
?>

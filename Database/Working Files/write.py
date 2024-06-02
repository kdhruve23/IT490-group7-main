import pika

# RabbitMQ server connection parameters
rabbitmq_host = '10.248.179.22'
rabbitmq_port = 5672  # Default RabbitMQ port
rabbitmq_user = 'database'
rabbitmq_password = 'password'
virtual_host = '/'

# Exchange name (frontend-backend-direct-exchange)
exchange_name = 'backend-database'  # Replace with the actual exchange name

# Routing key for the frontend queue
routing_key = 'backend'

# Message content
message_body = 'Hello, backend!'

try:
    # Create a connection to RabbitMQ
    credentials = pika.PlainCredentials(rabbitmq_user, rabbitmq_password)
    parameters = pika.ConnectionParameters(rabbitmq_host, rabbitmq_port, virtual_host, credentials)
    connection = pika.BlockingConnection(parameters)

    # Create a channel
    channel = connection.channel()

    # Declare the exchange (direct type)
    channel.exchange_declare(exchange=exchange_name, exchange_type='direct', durable=True)

    # Publish the message with the routing key
    channel.basic_publish(
        exchange=exchange_name,
        routing_key=routing_key,
        body=message_body,
        properties=pika.BasicProperties(
            delivery_mode=2,  # Make the message persistent
        )
    )

    print(f"Message sent to the exchange '{exchange_name}' with routing key '{routing_key}': {message_body}")

    # Close the channel and the connection
    connection.close()

except Exception as e:
    print(f"Error: {e}")


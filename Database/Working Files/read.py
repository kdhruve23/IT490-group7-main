import pika

# RabbitMQ server connection parameters
rabbitmq_host = '10.248.179.10' #if we don’t get a static ip address this has to be changed to my ip everytime
rabbitmq_port = 5672  # Default RabbitMQ port
rabbitmq_user = 'database'
rabbitmq_password = 'password'
virtual_host = '/'

# Queue name (backend-to-frontend-queue)
queue_name = 'back-data'  # Replace with the actual queue name

def callback(ch, method, properties, body):
    print(f"Received: {body}")

try:
    # Create a connection to RabbitMQ
    credentials = pika.PlainCredentials(rabbitmq_user, rabbitmq_password)
    parameters = pika.ConnectionParameters(rabbitmq_host, rabbitmq_port, virtual_host, credentials)
    connection = pika.BlockingConnection(parameters)

    # Create a channel
    channel = connection.channel()

    # Declare the queue
    channel.queue_declare(queue=queue_name, durable=True)

    print("Waiting for messages from the backend...")

    # Set up a callback function to process received messages
    channel.basic_consume(queue=queue_name, on_message_callback=callback, auto_ack=True)

    # Start consuming messages
    channel.start_consuming()

except Exception as e:
    print(f"Error: {e}")

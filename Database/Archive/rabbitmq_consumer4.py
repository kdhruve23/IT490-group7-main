import pika
import mysql.connector
import json
import time

# Set your RabbitMQ server connection parameters
rabbitmq_host = '10.147.17.34'  # Change this to your RabbitMQ server's hostname or IP
rabbitmq_port = 5672          # Change this to your RabbitMQ server's port

rabbitmq_params = pika.ConnectionParameters(
    host=rabbitmq_host,
    port=rabbitmq_port,
    credentials=pika.PlainCredentials(username='backend', password='password'),
)

# Establish a connection to RabbitMQ server
while True:
    try:
        connection = pika.BlockingConnection(rabbitmq_params)
        break
    except Exception as e:
        print(f"Connection to RabbitMQ failed. Retrying in 5 seconds... ({e})")
        time.sleep(5)

channel = connection.channel()

# Declare the queue you want to consume from
queue_name = 'back-data'
channel.queue_declare(queue=queue_name, durable=True)

# Set up the MySQL database connection parameters
db_host = '10.147.17.44'
db_port = 3306  # Change this to your database port
db_user = 'rp54'
db_password = 'Patel@123'
db_name = 'ShapeShift'

# Establish a connection to MySQL database
db_connection = mysql.connector.connect(
    host=db_host,
    port=db_port,
    user=db_user,
    password=db_password,
    database=db_name
)

# Create a cursor object to interact with the database
cursor = db_connection.cursor()

# Callback function to handle incoming messages
def callback(ch, method, properties, body):
    try:
        message = body.decode()
        print(f"Received message: {message}")

        # Assuming the message is a JSON string, you can load it into a Python dictionary
        data = json.loads(message)
        print("Parsed JSON:", data)

        # Check if the user already exists in the database
        check_sql = "SELECT * FROM users WHERE email = %s"
        cursor.execute(check_sql, (data['email'],))
        existing_user = cursor.fetchone()

        if existing_user:
            print("User already exists in the database.")
        else:
            # User doesn't exist, insert data into the MySQL database
            insert_sql = "INSERT INTO users (email, password, weight, height, goal, first_name, last_name) VALUES (%s, %s, %s, %s, %s, %s, %s)"
            values = (
                data['email'],
                data['password'],
                data['weight'],
                data['height'],
                data.get('goal', None),
                data['first_name'],
                data['last_name']
            )
            cursor.execute(insert_sql, values)
            db_connection.commit()
            print("Data written to the 'users' table.")

        # ... (rest of the code)

    except Exception as e:
        print(f"Error processing message: {e}")

# Set up the consumer and start consuming messages
try:
    channel.basic_consume(queue=queue_name, on_message_callback=callback, auto_ack=True)
    print('Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()

except KeyboardInterrupt:
    # Handle keyboard interrupt (CTRL+C)
    print('Interrupted. Closing connection.')
    channel.stop_consuming()

finally:
    # Ensure that the connection is properly closed
    connection.close()

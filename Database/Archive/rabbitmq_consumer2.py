import pika
import mysql.connector
import json
import time

# Set your RabbitMQ server connection parameters
rabbitmq_primary_host = '10.147.17.34'
rabbitmq_secondary_host = '10.147.17.79'
rabbitmq_port = 5672  # Change this to your RabbitMQ server's port

rabbitmq_params = pika.ConnectionParameters(
    host=[rabbitmq_primary_host, rabbitmq_secondary_host],
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

        if 'email' in data and 'password' in data:
            # Check if the user exists and the password is correct
            login_sql = "SELECT * FROM users WHERE email = %s AND password = %s"
            cursor.execute(login_sql, (data['email'], data['password']))
            user = cursor.fetchone()

            if user:
                print("Login success.")
                # Send all the field data back to the 'data-back' queue
                success_message = {"status": "success", "user_data": {
                    "user_id": user[0],
                    "email": user[1],
                    "password": user[2],
                    "weight": user[3],
                    "height": user[4],
                    "goal": user[5],
                    "first_name": user[6],
                    "last_name": user[7],
                    "movie": user[8],
                    "color": user[9]
                }}

                # Fetch nutritional data for the user
                nutrition_sql = "SELECT * FROM nutrition_data WHERE user_id = %s"
                cursor.execute(nutrition_sql, (user[0],))
                nutrition_data = cursor.fetchone()

                if not nutrition_data:
                    # If nutrition data doesn't exist, insert a record with initial values
                    insert_nutrition_sql = """
                        INSERT INTO nutrition_data (user_id, calories, protein, fat, carbohydrates, sugar, serving_size, servings)
                        VALUES (%s, 0, 0, 0, 0, 0, 0, 0)
                    """
                    cursor.execute(insert_nutrition_sql, (user[0],))
                    db_connection.commit()

                    # Set nutrition_data to the inserted record
                    nutrition_data = {
                        "user_id": user[0],
                        "calories": 0,
                        "protein": 0,
                        "fat": 0,
                        "carbohydrates": 0,
                        "sugar": 0,
                        "serving_size": 0,
                        "servings": 0
                    }

                success_message["nutrition_data"] = nutrition_data

                # Publish success message back to the 'data-back' queue
                channel.basic_publish(
                    exchange='',
                    routing_key='data-back',
                    body=json.dumps(success_message),
                    properties=pika.BasicProperties(
                        delivery_mode=2,  # Make the message persistent
                    )
                )
                print("Success message sent back to the 'data-back' queue.")
            else:
                print("Login failed.")
                # Send a no_success message back to the 'data-back' queue
                no_success_message = {"status": "no_success", "email": data['email']}
                channel.basic_publish(
                    exchange='',
                    routing_key='data-back',
                    body=json.dumps(no_success_message),
                    properties=pika.BasicProperties(
                        delivery_mode=2,  # Make the message persistent
                    )
                )
                print("No success message sent back to the 'data-back' queue.")
        else:
            print("Invalid login request format.")

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

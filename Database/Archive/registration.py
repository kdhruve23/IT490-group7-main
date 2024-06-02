import mysql.connector
import json
import pika

def setup_queues(channel):
    # Define registration request queue and response queue
    registration_request_queue = 'back-reg-request'
    registration_response_queue = 'data-reg-response'

    channel.queue_declare(queue=registration_request_queue, durable=True)
    channel.queue_declare(queue=registration_response_queue, durable=True)

    return registration_request_queue, registration_response_queue

def connect_to_database():
    db_host = '10.147.17.44'
    db_port = 3306
    db_user = 'rp54'
    db_password = 'Patel@123'
    db_name = 'ShapeShift'

    db_connection = mysql.connector.connect(
        host=db_host,
        port=db_port,
        user=db_user,
        password=db_password,
        database=db_name
    )

    cursor = db_connection.cursor()
    return db_connection, cursor

def handle_registration(data, cursor, db_connection, channel):
    user_exists_sql = "SELECT * FROM users WHERE email = %s"
    cursor.execute(user_exists_sql, (data['email'],))
    existing_user = cursor.fetchone()

    if existing_user:
        print("User already exists.")
        user_exists_message = {"status": "user_exists", "email": data['email']}
        channel.basic_publish(
            exchange='backend-database',
            routing_key='req.data',
            body=json.dumps(user_exists_message),
            properties=pika.BasicProperties(
                delivery_mode=2,
            )
        )
        print("User exists message sent back to the 'data-reg-response' queue.")
    else:
        registration_sql = """
            INSERT INTO users (email, password, weight, height, goal, first_name, last_name, movie, color)
            VALUES (%s, %s, %s, %s, '', %s, %s, %s, %s)
        """
        cursor.execute(registration_sql, (
            data['email'], data['password'], data['weight'], data['height'],
            data['first_name'], data['last_name'], data['movie'], data['color']
        ))
        db_connection.commit()

        print("Registration success. Data added to the database.")
        success_message = {"status": "success"}

        try:
            channel.basic_publish(
                exchange='backend-database',
                routing_key='reg.data',
                body=json.dumps(success_message),
                properties=pika.BasicProperties(
                    delivery_mode=2,
                )
            )
            print("Success message sent back to the 'data-reg-response' queue.")
        except Exception as e:
            print(f"Error sending success message: {e}")

if __name__ == "__main__":
    connection = pika.BlockingConnection(pika.ConnectionParameters('localhost'))
    channel = connection.channel()

    registration_request_queue, registration_response_queue = setup_queues(channel)
    db_connection, cursor = connect_to_database()
    channel.basic_consume(
        queue=registration_request_queue,
        on_message_callback=lambda ch, method, properties, body: handle_registration(json.loads(body), cursor, db_connection, channel),
        auto_ack=True
    )

    print('Waiting for registration messages. To exit press CTRL+C')
    channel.start_consuming()

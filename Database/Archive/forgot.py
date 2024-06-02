import json
import pika
import logging
import mysql.connector

logging.basicConfig(level=logging.INFO)
logging.getLogger('pika').setLevel(logging.WARNING)
logging.getLogger('urllib3').setLevel(logging.WARNING)

def handle_forgot_password(ch, method, properties, body, cursor, db_connection, channel):
    try:
        data = json.loads(body)

        email = data.get('email', '')
        color = data.get('color', '')
        movie = data.get('movie', '')
        password = data.get('password', '')

        print(f"Received Forgot Password Request: {data}")
        print(f"Email: {email}, Color: {color}, Movie: {movie}, New Password: {password}")

        # Check if the email, color, and movie match a user in the database
        user_query = "SELECT * FROM users WHERE email = %s AND color = %s AND movie = %s"
        cursor.execute(user_query, (email, color, movie))
        user = cursor.fetchone()

        if user:
            # Update the user's password with the new password
            update_password_query = "UPDATE users SET password = %s WHERE email = %s"
            print("Executing update_password_query...")
            cursor.execute(update_password_query, (password, email))
            print("Update complete. Committing changes...")
            db_connection.commit()
            print("Changes committed.")

            response_message = {"status": "success", "message": "Password updated successfully."}
        else:
            response_message = {"status": "no_success", "message": "Invalid email, color, or movie."}

        # Publish the response message
        channel.basic_publish(
            exchange='backend-database',
            routing_key='pass.data',
            body=json.dumps(response_message),
            properties=pika.BasicProperties(
                delivery_mode=2,
            )
        )
        print("Forgot Password response sent back to the 'data-pass-response' queue.")

    except Exception as e:
        logging.error(f"Error handling forgot password: {e}")

if __name__ == "__main__":
    try:
        # Establish connection to RabbitMQ
        connection_params = pika.ConnectionParameters(
            host='10.147.17.34',  # RabbitMQ primary host
            port=5672,
            credentials=pika.PlainCredentials(username='backend', password='password')
        )
        connection = pika.BlockingConnection(connection_params)
        channel = connection.channel()

        # Set up queue
        forgot_password_queue = 'back-pass-request'
        channel.queue_declare(queue=forgot_password_queue, durable=True)

        # Connect to MySQL database
        db_connection = mysql.connector.connect(
            host='10.147.17.44',  # MySQL host
            port=3306,
            user='rp54',
            password='Patel@123',
            database='ShapeShift'
        )
        cursor = db_connection.cursor()

        # Set up the consumer for forgot password queue
        channel.basic_consume(
            queue=forgot_password_queue,
            on_message_callback=lambda ch, method, properties, body: handle_forgot_password(ch, method, properties, body, cursor, db_connection, channel),
            auto_ack=True
        )

        print('Waiting for forgot password messages. To exit press CTRL+C')
        channel.start_consuming()

    except KeyboardInterrupt:
        print('Interrupted. Closing connection.')
        channel.stop_consuming()

    finally:
        connection.close()
        db_connection.close()


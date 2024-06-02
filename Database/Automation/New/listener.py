import os
import pika
import json
import logging
import mysql.connector
from datetime import datetime

# Import other scripts
from registration import handle_registration, connect_to_database as reg_connect_to_database
from login import handle_login, connect_to_database as login_connect_to_database
from forgot import handle_forgot_password
from meals import handle_meals, connect_to_database as meals_connect_to_database

class DateTimeEncoder(json.JSONEncoder):
    def default(self, o):
        if isinstance(o, datetime):
            return o.isoformat()
        return super().default(o)

logging.basicConfig(level=logging.INFO)
logging.getLogger('pika').setLevel(logging.WARNING)
logging.getLogger('urllib3').setLevel(logging.WARNING)

def connect_to_rabbitmq():
    rabbitmq_primary_host = '10.147.17.34'
    rabbitmq_secondary_host = '10.147.17.79'
    rabbitmq_port = 5672

    rabbitmq_hosts = [rabbitmq_primary_host, rabbitmq_secondary_host]

    connection = None
    channel = None

    for host in rabbitmq_hosts:
        rabbitmq_params = pika.ConnectionParameters(
            host=host,
            port=rabbitmq_port,
            credentials=pika.PlainCredentials(username='backend', password='password'),
        )

        try:
            connection = pika.BlockingConnection(rabbitmq_params)
            channel = connection.channel()
            print(f"Successfully connected to RabbitMQ host: {host}")
            break
        except Exception as e:
            print(f"Connection to RabbitMQ failed for host {host}. Error: {e}")
            print(f"Attempting the next host...")

    if not connection or not channel:
        print("Failed to connect to RabbitMQ. Please check your network connection and try again.")
        exit()

    return connection, channel

def setup_queues(channel):
    registration_request_queue = 'back-reg-request'
    login_request_queue = 'back-login-request'
    forgot_password_request_queue = 'back-pass-request'
    meals_request_queue = 'back-meals-request'
    data_meals_response_queue = 'data-meals-response'

    channel.queue_declare(queue=registration_request_queue, durable=True)
    channel.queue_declare(queue=login_request_queue, durable=True)
    channel.queue_declare(queue=forgot_password_request_queue, durable=True)
    channel.queue_declare(queue=meals_request_queue, durable=True)
    channel.queue_declare(queue=data_meals_response_queue, durable=True)

    return (
        registration_request_queue,
        login_request_queue,
        forgot_password_request_queue,
        meals_request_queue,
        data_meals_response_queue
    )

if __name__ == "__main__":
    connection, channel = connect_to_rabbitmq()

    reg_db_connection, reg_cursor = reg_connect_to_database()
    login_db_connection, login_cursor = login_connect_to_database()
    meals_db_connection, meals_cursor = meals_connect_to_database()

    reg_queue, login_queue, forgot_password_queue, meals_request_queue, data_meals_response_queue = setup_queues(channel)

    try:
        channel.basic_consume(
            queue=reg_queue,
            on_message_callback=lambda ch, method, properties, body: handle_registration(
                json.loads(body), reg_cursor, reg_db_connection, channel),
            auto_ack=True
        )

        channel.basic_consume(
            queue=login_queue,
            on_message_callback=lambda ch, method, properties, body: handle_login(
                ch, method, properties, body, login_cursor, login_db_connection, channel),
            auto_ack=True
        )

        channel.basic_consume(
            queue=forgot_password_queue,
            on_message_callback=lambda ch, method, properties, body: handle_forgot_password(
                ch, method, properties, body, login_cursor, login_db_connection, channel),
            auto_ack=True
        )

        channel.basic_consume(
            queue=meals_request_queue,
            on_message_callback=lambda ch, method, properties, body: handle_meals(
                ch, method, properties, body, meals_cursor, meals_db_connection, channel),
            auto_ack=True
        )

        print('Waiting for messages. To exit press CTRL+C')
        channel.start_consuming()

    except KeyboardInterrupt:
        print('Interrupted. Closing connection.')
        channel.stop_consuming()

    finally:
        connection.close()
        reg_db_connection.close()
        login_db_connection.close()
        meals_db_connection.close()


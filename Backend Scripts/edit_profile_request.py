#!/usr/bin/env python3
import os
import pika
import json

os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'Project490.settings')

#credentials = pika.PlainCredentials('backend', 'password')
#parameters = pika.ConnectionParameters(
#    host='10.147.17.79',
    #host='10.147.17.34',
#    port=5672,
#    credentials=credentials)

primary_host = '10.147.17.79'
secondary_host = '10.147.17.34'

credentials = pika.PlainCredentials('backend', 'password')

def check_rabbitmq_host(host):
    try:
        parameters = pika.ConnectionParameters(host=host, port=5672, credentials=credentials)
        connection = pika.BlockingConnection(parameters)
        connection.close()
        return True
    except pika.exceptions.AMQPConnectionError:
        return False

if not check_rabbitmq_host(primary_host):
    print(f"Primary host {primary_host} is not running RabbitMQ. Setting secondary host {secondary_host}.")
    parameters = pika.ConnectionParameters(host=secondary_host, port=5672, credentials=credentials)
else:
    parameters = pika.ConnectionParameters(host=primary_host, port=5672, credentials=credentials)

# Create connections and channels
consume1_connection = pika.BlockingConnection(parameters)
consume1_channel = consume1_connection.channel()
consume1_channel.queue_declare(queue='front-profile-request', durable=True)
consume1_channel.queue_bind(exchange='frontend-backend', queue='front-profile-request', routing_key='profile.request')

forward1_connection = pika.BlockingConnection(parameters)
forward1_channel = forward1_connection.channel()
forward1_channel.queue_declare(queue='back-profile-request', durable=True)
forward1_channel.queue_bind(exchange='backend-database', queue='back-profile-request', routing_key='profile.back')


def callback1(ch, method, properties, body):
    data = json.loads(body.decode('utf-8'))
    print("Received login request from front-end:", data)
    try:
        firstname = data.get('firstname')
        lastname = data.get('lastname')
        email = data.get('email')
        password = data.get('password')
        height = data.get('height')
        weight = data.get('weight')
        print("Processing Data:", firstname, lastname, email, password, height, weight)

        # Publish data to forward1 database
        try:
            forward1_channel.basic_publish(
                exchange='backend-database',
                routing_key='profile.back',
                body=json.dumps(data),
properties=pika.BasicProperties(delivery_mode=2)
            )
            print("User data sent to RabbitMQ", data)
        except keyboardInterrupt:
            print(' [*] Exiting due to user interruptions')
    except Exception as e:
        print(f"Error processing message: {e}")

consume1_channel.basic_consume(queue='front-profile-request', on_message_callback=callback1, auto_ack=True)

try:
    print('Profile Editing is [*] Waiting for messages. To exit press CTRL+C')
    consume1_channel.start_consuming()

except KeyboardInterrupt:
    consume1_channel.stop_consuming()

consume1_connection.close()
forward1_connection.close()

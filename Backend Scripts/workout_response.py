#!/usr/bin/env python3

import os
import django
import pika
import sys
import json
from django.db import OperationalError, DatabaseError

os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'Project490.settings')
#credentials = pika.PlainCredentials('backend','password')
#parameters = pika.ConnectionParameters(
#    host='10.147.17.79',
   # host='10.147.17.34',
#    port=5672,
#    credentials = credentials)

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

#consuming from database
connection = pika.BlockingConnection(parameters)
channel = connection.channel()
channel.queue_declare(queue='data-workout-response', durable=True)
channel.queue_bind(exchange='backend-database', queue='data-workout-response', routing_key='workout.data')


#publishing rabbitmq data to frontend
bd_rbqm = pika.BlockingConnection(parameters)
bd_channel = bd_rbqm.channel()
bd_channel.queue_declare(queue='front-workout-response', durable=True)
bd_channel.queue_bind(exchange='frontend-backend', queue='front-workout-response', routing_key='workout.response')




def callback(ch, method, properties, body):
    data = json.loads(body.decode('utf-8'))
    print("Received data:", data)
    try:
        message = data.get('status')
        print("Processing data:", message)
        try:
            bd_channel.basic_publish(
                exchange='frontend-backend',
                routing_key='workout.response',
                body=json.dumps(data),
                properties=pika.BasicProperties(
                     delivery_mode=2,
                )
            )
            print("data sent to frontend")
        except (OperationalError, DatabaseError) as e:
            print(f"Error connecting to the default database: {e}")
    except KeyError as e:
        print(f"Error processing message: {e}")

channel.basic_consume(
        queue='data-workout-response', on_message_callback=callback, auto_ack=True)

try:
    print('Consuming Workout [*] Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()
except KeyboardInterrupt:
    print(' [*] Exiting due to user interruption')
except Exception as e:
    print(f"Error during message consumption: {e}")

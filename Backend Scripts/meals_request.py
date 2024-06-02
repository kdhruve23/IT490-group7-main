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
#    credentials = credentials
# )

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

connection = pika.BlockingConnection(parameters)
channel = connection.channel()
channel.queue_declare(queue='front-meals-request', durable=True)
channel.queue_bind(exchange='frontend-backend', queue='front-meals-request', routing_key='meals.request')


#publishing rabbitmq data to database
bd_rbqm = pika.BlockingConnection(parameters)
bd_channel = bd_rbqm.channel()
bd_channel.queue_declare(queue='back-meals-request', durable=True)
bd_channel.queue_bind(exchange='backend-database', queue='back-meals-request', routing_key='meals.back')

def callback(ch, method, properties, body):
    data = json.loads(body.decode('utf-8'))
    print("Received data:", data)
    try:
        email = data.get('email')
        meal_name = data.get('meal_name')
        calories = data.get('calories')
        protein = data.get('protein')
        carbohydrates = data.get('carbohydrates')
        fat = data.get('fat')
        sugar = data.get('sugar')
        servingSize = data.get('servingSize')
        servings = data.get('servings')
        user_id = data.get('user_id')
        meal_datetime = data.get('mealDateTime')

        print("Processing data:", email, meal_name, calories, protein, carbohydrates, fat, sugar, servingSize, servings, user_id, meal_datetime)

        try:
            bd_channel.basic_publish(
                exchange='backend-database',
                routing_key='meals.back',
                body=json.dumps(data),
                properties=pika.BasicProperties(
                     delivery_mode=2,
                )
            )
            print("data sent to first database")

        except (OperationalError, DatabaseError) as e:
            print(f"Error connecting to the default database: {e}")
    except KeyError as e:
        print(f"Error processing message: {e}")

channel.basic_consume(
        queue='front-meals-request', on_message_callback=callback, auto_ack=True)

try:
    print('Meals is [*] Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()
except KeyboardInterrupt:
    print(' [*] Exiting due to user interruption')
except Exception as e:
    print(f"Error during message consumption: {e}")


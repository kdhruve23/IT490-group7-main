#!/usr/bin/env python3

import os
import django
import pika
import sys
import json
from django.db import OperationalError, DatabaseError

os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'Project490.settings')
credentials = pika.PlainCredentials('backend','password')
parameters = pika.ConnectionParameters(
    host='10.248.179.10',
    port=5672,
    credentials = credentials
 )

try:
    django.setup()

except Exception as e:
    print("Some modules are missing {}".format(e))

#from users.models import CustomUser
from users.utils import save_user_info

connection = pika.BlockingConnection(parameters)

channel = connection.channel()
channel.queue_declare(queue='front-back', durable=True)


#publishing rabbitmq data to database
bd_rbqm = pika.BlockingConnection(parameters)
bd_channel = bd_rbqm.channel()
bd_channel.queue_declare(queue='back-data', durable=True)


def callback(ch, method, properties, body):
    data = json.loads(body.decode('utf-8'))
    print("Received data:", data)
    try:
        first_name = data.get('first')
        last_name = data.get('last')
        email = data.get('email')
        password = data.get('password')
        print("Processing data:", first_name, last_name, email)

        try:
            save_user_info(first_name, last_name, email, password)
        
            bd_channel.basic_publish(
                exchange='backend-database',
                routing_key='database',
                body=json.dumps(data),
                properties=pika.BasicProperties(
                     delivery_mode=2,
                )
            )
            print("User data sent to RabbitMQ:", data)
        except (OperationalError, DatabaseError) as e:
            print(f"Error connecting to the default database: {e}")
       
    except KeyError as e:
        print(f"Error processing message: {e}")

channel.basic_consume(
        queue='front-back', on_message_callback=callback, auto_ack=True)

try:
    print(' [*] Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()
except KeyboardInterrupt:
    print(' [*] Exiting due to user interruption')
except Exception as e:
    print(f"Error during message consumption: {e}")
#finally:
#    if bd_rbqm.is_open:
#        bd_rbqm.close()
#    if connection.is_open:
#        connection.close()



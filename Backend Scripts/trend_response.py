#!/usr/bin/env python3
import os
import pika
import json

os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'Project490.settings')

#credentials = pika.PlainCredentials('backend', 'password')
#parameters = pika.ConnectionParameters(
#      host='10.147.17.79',
     # host='10.147.17.34',
#      port=5672,
#      credentials=credentials)

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


consume2_connection = pika.BlockingConnection(parameters)
consume2_channel = consume2_connection.channel()
consume2_channel.queue_declare(queue='data-trend-response', durable=True)
consume2_channel.queue_bind(exchange='backend-database', queue='data-trend-response', routing_key='trend.data')

forward2_connection = pika.BlockingConnection(parameters)
forward2_channel = forward2_connection.channel()
forward2_channel.queue_declare(queue='front-trend-response', durable=True)
forward2_channel.queue_bind(exchange='frontend-backend', queue='front-trend-response', routing_key='trend.response')


def callback2(ch, method, properties, body):
    data = json.loads(body.decode('utf-8'))
    print("Received data from database:", data)

    # Publish data to forward2 queue
    forward2_channel.basic_publish(
        exchange='frontend-backend',
        routing_key='trend.response',
        body=json.dumps(data),
        properties=pika.BasicProperties(delivery_mode=2)
    )

consume2_channel.basic_consume(queue='data-trend-response', on_message_callback=callback2, auto_ack=True)


try:
    print('Consuming Login [*] Waiting for messages. To exit press CTRL+C')
    consume2_channel.start_consuming()
except KeyboardInterrupt:
    consume2_channel.stop_consuming()


consume2_connection.close()
forward2_connection.close()

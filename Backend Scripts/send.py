#!/usr/bin/env python3

try:
    import pika
except Exception as e:
    print("some Modules are missing {}".format_map(e))

credentials = pika.PlainCredentials('backend','password')
connection = pika.BlockingConnection(
    pika.ConnectionParameters(host='10.248.179.6', credentials = credentials))

channel = connection.channel()

channel.queue_declare(queue='hello')

channel.basic_publish(exchange='', routing_key='hello', body='Hello World!')

print("Message 'Hello World' is Published")
connection.close()


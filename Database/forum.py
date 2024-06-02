import os
import pika
import json
import pymysql
from datetime import datetime
from decimal import Decimal

class DateTimeEncoder(json.JSONEncoder):
    def default(self, o):
        if isinstance(o, datetime):
            return o.isoformat()
        elif isinstance(o, Decimal):
            return float(o)
        return super(DateTimeEncoder, self).default(o)

# credentials = pika.PlainCredentials('backend', 'password')
# parameters = pika.ConnectionParameters(
#     host='10.147.17.79',
#     # host='10.147.17.34',
#     port=5672,
#     credentials=credentials)

primary_host = '10.147.17.79'
secondary_host = '10.147.17.34'
output_file = 'forum_data.txt'  # Specify your desired file name or path here

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
consume1_channel.queue_declare(queue='back-forum-request', durable=True)
consume1_channel.queue_bind(exchange='backend-database', queue='back-forum-request', routing_key='forum.back')

def save_to_txt(data):
    with open(output_file, 'w') as file:
        file.write(json.dumps(data) + '\n')

def get_mysql_connection():
    # Modify this with your MySQL connection details
    connection = pymysql.connect(
        host='10.147.17.44',
        user='rp54',
        password='Patel@123',
        database='ShapeShift',
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )
    return connection, connection.cursor()

def get_all_posts(cursor):
    post_sql = "SELECT user_id, first_name, post_content FROM posts"
    cursor.execute(post_sql)
    posts = cursor.fetchall()
    return posts

def callback_and_insert(ch, method, properties, body):
    data = json.loads(body.decode('utf-8'))
    print("Received data from backend:", data)

    try:
        email = data.get('email')
        post_content = data.get('post')
        print("Processing Data:", email, post_content)

        # Connect to MySQL
        db_connection, cursor = get_mysql_connection()

        try:
            # Insert post into the 'posts' table
            if email and post_content:
                user_sql = "SELECT user_id, first_name FROM users WHERE email = %s"
                cursor.execute(user_sql, (email,))
                user = cursor.fetchone()

                if user:
                    insert_post_sql = "INSERT INTO posts (user_id, first_name, post_content) VALUES (%s, %s, %s)"
                    cursor.execute(insert_post_sql, (user['user_id'], user['first_name'], post_content))
                    db_connection.commit()
                    print(f"Post added for user {email}")

            # Fetch all posts for all users
            all_posts = get_all_posts(cursor)

            # Send success message back to the queue with all post_content
            success_message = {
                'status': 'success',
                'message': f'Successfully added post for {email}',
                'all_posts': all_posts
            }
            ch.basic_publish(
                exchange='backend-database',
                routing_key='forum.data',
                body=json.dumps(success_message, cls=DateTimeEncoder),
                properties=pika.BasicProperties(
                    delivery_mode=2,
                )
            )

        except Exception as e:
            print(f"Error reading and updating database: {e}")

        finally:
            db_connection.close()

        save_to_txt(data)

    except Exception as e:
        print(f"Error processing message: {e}")

if __name__ == "__main__":
    consume1_channel.basic_consume(queue='back-forum-request', on_message_callback=callback_and_insert, auto_ack=True)

    try:
        print('Post is [*] Waiting for messages. To exit press CTRL+C')
        consume1_channel.start_consuming()

    except KeyboardInterrupt:
        consume1_channel.stop_consuming()

    consume1_connection.close()


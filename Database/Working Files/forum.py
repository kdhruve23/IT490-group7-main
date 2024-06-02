import os
import pika
import json
import pymysql
from datetime import datetime
import traceback

class DateTimeEncoder(json.JSONEncoder):
    def default(self, o):
        if isinstance(o, datetime):
            return o.isoformat()
        return super(DateTimeEncoder, self).default(o)

def connect_to_rabbitmq():
    credentials = pika.PlainCredentials('backend', 'password')
    parameters = pika.ConnectionParameters(
        host='10.147.17.79',
        port=5672,
        credentials=credentials
    )
    connection = pika.BlockingConnection(parameters)
    channel = connection.channel()
    return connection, channel

def callback_and_insert(ch, method, properties, body, cursor, db_connection, channel):
    data = json.loads(body.decode('utf-8'))
    print("Received data from backend:", data)

    try:
        email = data.get('email')
        post_content = data.get('post')
        print("Processing Data:", email, post_content)

        # Connect to MySQL
        user_sql = "SELECT * FROM users WHERE email = %s"
        cursor.execute(user_sql, (email,))
        user = cursor.fetchone()

        if user:
            # Insert the post into the 'posts' table
            insert_post_sql = "INSERT INTO posts (user_id, post_content) VALUES (%s, %s)"
            cursor.execute(insert_post_sql, (user['user_id'], post_content))
            db_connection.commit()
            print(f"Post added for user {email}")

            # Fetch all posts
            all_posts = get_all_posts(cursor)

            # Send success message back to the queue with user information and all posts
            success_message = {
                'status': 'success',
                'message': f'Successfully added post for {email}',
                'user_data': {
                    'user_id': user['user_id'],
                    'email': user['email'],
                    'password': user['password'],
                    'weight': user['weight'],
                    'height': user['height'],
                    'goal': user['goal'],
                    'first_name': user['first_name'],
                    'last_name': user['last_name'],
                },
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

        else:
            print(f"User with email {email} not found in the database. Skipping post addition.")

    except Exception as e:
        print(f"Error processing message: {e}")
        traceback.print_exc()

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

if __name__ == "__main__":
    connection, channel = connect_to_rabbitmq()

    queue_name = 'back-forum-request'
    channel.queue_declare(queue=queue_name, durable=True)

    db_connection, cursor = get_mysql_connection()

    try:
        channel.basic_consume(
            queue=queue_name,
            on_message_callback=lambda ch, method, properties, body: callback_and_insert(ch, method, properties, body, cursor, db_connection, channel),
            auto_ack=True
        )
        print('forum is [*] Waiting for messages. To exit press CTRL+C')
        channel.start_consuming()

    except KeyboardInterrupt:
        print('Interrupted. Closing connection.')
        channel.stop_consuming()

    finally:
        connection.close()
        db_connection.close()


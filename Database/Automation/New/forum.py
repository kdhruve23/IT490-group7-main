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

def callback_and_insert(ch, method, properties, body, cursor, db_connection, channel):
    login_sql = "SELECT * FROM users WHERE email = %s AND password = %s"
    data = json.loads(body)
    hashed_password = hash_password(data['password'])
    cursor.execute(login_sql, (data['email'], hashed_password))
    user = cursor.fetchone()

    response_message = {"status": "success" if user else "no_success"}

    if user:
        response_message["user_data"] = {
            "user_id": user[0],
            "email": user[1],
            "password": user[2],
            "weight": user[3],
            "height": user[4],
            "goal": user[5],
            "first_name": user[6],
            "last_name": user[7],
            "movie": user[8],
            "color": user[9],
        }

        # Fetch all posts for all users
        all_posts = get_all_posts(cursor)
        response_message["all_posts"] = all_posts

        # Fetch meals for the user
        meals_sql = "SELECT * FROM meals WHERE user_id = %s"
        cursor.execute(meals_sql, (user[0],))
        meals_data = cursor.fetchall()
        response_message["meals_data"] = [
            {
                "meal_id": row[0],
                "user_id": row[1],
                "meal_name": row[2],
                "meal_datetime": row[3].isoformat() if row[3] else None
            } for row in meals_data
        ]

        # Fetch nutrition data
        nutrition_sql = """
            SELECT nd.*
            FROM nutrition_data nd
            JOIN meals m ON nd.meal_id = m.meal_id
            WHERE m.user_id = %s
        """
        cursor.execute(nutrition_sql, (user[0],))
        nutrition_data = cursor.fetchall()
        response_message["nutrition_data"] = [
            {
                "data_id": row[0],
                "meal_id": row[1],
                "calories": row[2],
                "protein": row[3],
                "fat": row[4],
                "carbohydrates": row[5],
                "sugar": row[6],
                "serving_size": row[7],
                "servings": row[8]
            } for row in nutrition_data
        ]

        # Fetch user workouts
        workouts = get_user_workouts(user[0], cursor)
        response_message["workouts"] = [
            {
                "workout_id": row[0],
                "user_id": row[1],
                "workout_name": row[2],
                "created_at": row[3]
            } for row in workouts
        ]

        # Get user totals
        user_totals = get_user_totals(user[0], cursor)
        response_message["user_totals"] = user_totals

    # Use the DateTimeEncoder when serializing the response_message to JSON
    response_message_json = json.dumps(response_message, cls=DateTimeEncoder)

    # Publish the response message
    channel.basic_publish(
        exchange='backend-database',
        routing_key='log.data',
        body=response_message_json,
        properties=pika.BasicProperties(
            delivery_mode=2,
        )
    )
    print("Login response sent back to the 'log.data' queue.")

if __name__ == "__main__":
    consume1_channel.basic_consume(queue='back-forum-request', on_message_callback=callback_and_insert, auto_ack=True)

    try:
        print('Post is [*] Waiting for messages. To exit press CTRL+C')
        consume1_channel.start_consuming()

    except KeyboardInterrupt:
        consume1_channel.stop_consuming()

    consume1_connection.close()


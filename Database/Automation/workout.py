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

credentials = pika.PlainCredentials('backend', 'password')
parameters = pika.ConnectionParameters(
    host='10.147.17.79',
    port=5672,
    credentials=credentials
)

def connect_to_rabbitmq():
    connection = pika.BlockingConnection(parameters)
    channel = connection.channel()
    return connection, channel

def save_to_txt(data):
    output_file = 'output_data.txt'
    with open(output_file, 'w') as file:
        file.write(json.dumps(data) + '\n')

def get_mysql_connection():
    connection = pymysql.connect(
        host='10.147.17.44',
        user='rp54',
        password='Patel@123',
        database='ShapeShift',
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )
    return connection, connection.cursor()

def get_user_workouts(user_id, cursor):
    workout_sql = "SELECT workout_id, user_id, workout_name, created_at FROM workout WHERE user_id = %s"
    cursor.execute(workout_sql, (user_id,))
    workouts = cursor.fetchall()
    return workouts

def get_user_totals(user_id, cursor):
    query = """
        SELECT SUM(calories) as total_calories,
               SUM(protein) as total_protein,
               SUM(carbohydrates) as total_carbohydrates,
               SUM(fat) as total_fat,
               SUM(sugar) as total_sugar
        FROM nutrition_data
        JOIN meals ON nutrition_data.meal_id = meals.meal_id
        WHERE meals.user_id = %s
    """
    cursor.execute(query, (user_id,))
    result = cursor.fetchone()
    return result

def handle_workout(ch, method, properties, body, cursor, db_connection, channel):
    data = json.loads(body.decode('utf-8'))
    email = data.get('email')
    workout = data.get('workout')

    try:
        user_sql = "SELECT * FROM users WHERE email = %s"
        cursor.execute(user_sql, (email,))
        user = cursor.fetchone()

        if user:
            # Insert workout information into the 'workout' table
            insert_workout_sql = "INSERT INTO workout (user_id, workout_name) VALUES (%s, %s)"
            cursor.execute(insert_workout_sql, (user['user_id'], workout))
            db_connection.commit()
            print(f"Workout '{workout}' added for user {email}")

            # Fetch workouts for the user
            workouts = get_user_workouts(user['user_id'], cursor)

            # Fetch user totals
            user_totals = get_user_totals(user['user_id'], cursor)

            # Send success message back to the queue with user information, workouts, and user totals
            success_message = {
                'status': 'success',
                'message': f'Successfully added workout "{workout}" for {email}',
                'user_data': {
                	'user_id': user['user_id']	
                    'email': user['email'],
                    'password': user['password'],
                    'weight': user['weight'],
                    'height': user['height'],
                    'goal': user['goal'],
                    'first_name': user['first_name'],
                    'last_name': user['last_name'],
                },
                'workouts': workouts,
                'user_totals': user_totals
            }
            ch.basic_publish(
                exchange='backend-database',
                routing_key='workout.data',
                body=json.dumps(success_message, cls=DateTimeEncoder),
                properties=pika.BasicProperties(
                    delivery_mode=2,
                )
            )

        else:
            print(f"User with email {email} not found in the database. Skipping workout addition.")

    except Exception as e:
        print(f"Error reading and updating database: {e}")

    save_to_txt(data)

if __name__ == "__main__":
    connection, channel = connect_to_rabbitmq()

    queue_name = 'back-workout-request'
    channel.queue_declare(queue=queue_name, durable=True)

    db_connection, cursor = get_mysql_connection()

    try:
        channel.basic_consume(
            queue=queue_name,
            on_message_callback=lambda ch, method, properties, body: handle_workout(ch, method, properties, body, cursor, db_connection, channel),
            auto_ack=True
        )
        print('Workout is [*] Waiting for messages. To exit press CTRL+C')
        channel.start_consuming()

    except KeyboardInterrupt:
        print('Interrupted. Closing connection.')
        channel.stop_consuming()

    finally:
        connection.close()
        db_connection.close()


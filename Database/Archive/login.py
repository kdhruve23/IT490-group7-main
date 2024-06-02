import mysql.connector
import json
import pika
import logging
from datetime import datetime

class DateTimeEncoder(json.JSONEncoder):
    def default(self, o):
        if isinstance(o, datetime):
            return o.isoformat()
        return super().default(o)

logging.basicConfig(level=logging.INFO)
logging.getLogger('pika').setLevel(logging.WARNING)
logging.getLogger('urllib3').setLevel(logging.WARNING)

def connect_to_database():
    db_host = '10.147.17.44'
    db_port = 3306
    db_user = 'rp54'
    db_password = 'Patel@123'
    db_name = 'ShapeShift'

    db_connection = mysql.connector.connect(
        host=db_host,
        port=db_port,
        user=db_user,
        password=db_password,
        database=db_name
    )

    cursor = db_connection.cursor()
    return db_connection, cursor

def connect_to_rabbitmq():
    rabbitmq_primary_host = '10.147.17.34'
    rabbitmq_secondary_host = '10.147.17.79'
    rabbitmq_port = 5672

    rabbitmq_hosts = [rabbitmq_primary_host, rabbitmq_secondary_host]

    connection = None
    channel = None

    for host in rabbitmq_hosts:
        rabbitmq_params = pika.ConnectionParameters(
            host=host,
            port=rabbitmq_port,
            credentials=pika.PlainCredentials(username='backend', password='password'),
        )

        try:
            connection = pika.BlockingConnection(rabbitmq_params)
            channel = connection.channel()
            break
        except Exception as e:
            logging.error(f"Connection to RabbitMQ failed for host {host}. Error: {e}")

    if not connection or not channel:
        logging.error("Unable to connect to any RabbitMQ host. Exiting.")
        exit()

    return connection, channel

def setup_login_queue(channel):
    login_request_queue_name = 'back-login-request'
    login_response_queue_name = 'data-login-response'

    channel.queue_declare(queue=login_request_queue_name, durable=True)
    channel.queue_declare(queue=login_response_queue_name, durable=True)

    return login_request_queue_name, login_response_queue_name

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

    if result:
        return {
            "total_calories": result[0] or 0,
            "total_protein": result[1] or 0,
            "total_carbohydrates": result[2] or 0,
            "total_fat": result[3] or 0,
            "total_sugar": result[4] or 0
        }
    else:
        return {
            "total_calories": 0,
            "total_protein": 0,
            "total_carbohydrates": 0,
            "total_fat": 0,
            "total_sugar": 0
        }

def handle_login(ch, method, properties, body, cursor, db_connection, channel):
    login_sql = "SELECT * FROM users WHERE email = %s AND password = %s"
    data = json.loads(body)
    cursor.execute(login_sql, (data['email'], data['password']))
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

        # Fetch meals
        #meals_sql = "SELECT * FROM meals WHERE user_id = %s"
        #cursor.execute(meals_sql, (user[0],))
        #meals_data = cursor.fetchall()
        #response_message["meals_data"] = [
        #    {"meal_id": row[0], "user_id": row[1], "meal_name": row[2], "meal_datetime": row[3]} for row in meals_data
        #]

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
    connection, channel = connect_to_rabbitmq()

    queue_name = setup_login_queue(channel)
    logging.info(f"Declared queues: {queue_name}")

    db_connection, cursor = connect_to_database()

    try:
        channel.basic_consume(
            queue=queue_name[0],
            on_message_callback=lambda ch, method, properties, body: handle_login(ch, method, properties, body, cursor, db_connection, channel),
            auto_ack=True
        )
        logging.info('Waiting for messages. To exit press CTRL+C')
        channel.start_consuming()

    except KeyboardInterrupt:
        logging.info('Interrupted. Closing connection.')
        channel.stop_consuming()

    finally:
        connection.close()
        db_connection.close()

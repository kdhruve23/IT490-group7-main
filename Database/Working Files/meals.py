import json
import pika
import mysql.connector
import logging
from datetime import datetime

def setup_queues(channel):
    meals_request_queue = 'back-meals-request'
    meals_response_queue = 'data-meals-response'

    channel.queue_declare(queue=meals_request_queue, durable=True)
    channel.queue_declare(queue=meals_response_queue, durable=True)

    return meals_request_queue, meals_response_queue

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

def get_user_workouts(user_id, cursor):
    workout_sql = "SELECT * FROM workout WHERE user_id = %s"
    cursor.execute(workout_sql, (user_id,))
    workouts = cursor.fetchall()
    return workouts

def get_all_posts(cursor):
    post_sql = "SELECT user_id, first_name, post_content FROM posts"
    cursor.execute(post_sql)
    posts = cursor.fetchall()
    return posts

def get_user_data(user_id, cursor):
    user_sql = "SELECT * FROM users WHERE user_id = %s"
    cursor.execute(user_sql, (user_id,))
    user_data = cursor.fetchone()

    return {
        "user_id": user_data[0],
        "email": user_data[1],
        "password": user_data[2],
        "weight": user_data[3],
        "height": user_data[4],
        "goal": user_data[5],
        "first_name": user_data[6],
        "last_name": user_data[7],
        "movie": user_data[8],
        "color": user_data[9],
    }

def handle_meals(ch, method, properties, body, cursor, db_connection, channel):
    try:
        data = json.loads(body.decode('utf-8'))

        # Extract meal data from the received message
        meal_name = data.get('meal_name')  # Change to match the key in the frontend message
        user_id = data.get('user_id')
        nutrition_data = {
            'calories': data.get('calories', 0),
            'protein': data.get('protein', 0),
            'carbohydrates': data.get('carbohydrates', 0),
            'fat': data.get('fat', 0),
            'sugar': data.get('sugar', 0),
            'serving_size': data.get('servingSize', 0),  # Change to match the key in the frontend message
            'servings': data.get('servings', 0),
        }

        # Fetch workouts for the user
        workouts = get_user_workouts(user_id, cursor)

        try:
            # Insert data into 'meals' table
            meal_insert_sql = """
                INSERT INTO meals (user_id, meal_name, meal_datetime)
                VALUES (%s, %s, %s)
            """
            cursor.execute(meal_insert_sql, (user_id, meal_name, datetime.now()))
            db_connection.commit()

            # Retrieve the last inserted meal_id
            meal_id = cursor.lastrowid

            # Insert data into 'nutrition_data' table
            nutrition_insert_sql = """
                INSERT INTO nutrition_data (meal_id, calories, protein, carbohydrates, fat, sugar, serving_size, servings)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
            """
            cursor.execute(nutrition_insert_sql, (
                meal_id,
                nutrition_data.get('calories', 0),
                nutrition_data.get('protein', 0),
                nutrition_data.get('carbohydrates', 0),
                nutrition_data.get('fat', 0),
                nutrition_data.get('sugar', 0),
                nutrition_data.get('serving_size', 0),
                nutrition_data.get('servings', 0)
            ))
            db_connection.commit()

            # Get user totals
            user_totals = get_user_totals(user_id, cursor)

            # Get user data
            user_data = get_user_data(user_id, cursor)
            all_posts = get_all_posts(cursor)

            meals_sql = "SELECT * FROM meals WHERE user_id = %s"
            cursor.execute(meals_sql, (user_id,))
            meals_data = cursor.fetchall()
            success_message = {
                "status": "success",
                "user_totals": user_totals,
                "user_data": user_data,
                "user_id": user_id,
                'workouts': workouts,
                "all_posts": all_posts,
                "meals_data": [
                    {
                        "meal_id": row[0],
                        "user_id": row[1],
                        "meal_name": row[2],
                        "meal_datetime": row[3].isoformat() if row[3] else None
                    } for row in meals_data
                ],
            }

            # Fetch nutrition data
            nutrition_sql = """
                SELECT nd.*
                FROM nutrition_data nd
                JOIN meals m ON nd.meal_id = m.meal_id
                WHERE m.user_id = %s
            """
            cursor.execute(nutrition_sql, (user_id,))
            nutrition_data = cursor.fetchall()
            success_message["nutrition_data"] = [
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

            print("Meal data successfully added to the database.")
            try:
                # Convert datetime to string before sending as JSON
                success_message_json = json.dumps(success_message, default=str)

                channel.basic_publish(
                    exchange='backend-database',
                    routing_key='meals.data',
                    body=success_message_json,
                    properties=pika.BasicProperties(
                        delivery_mode=2,
                    )
                )
                print("Success message sent back to the 'data-meals-response' queue.")
            except Exception as e:
                print(f"Error sending success message: {e}")

        except Exception as e:
            print(f"Error handling meals: {e}")

    except Exception as e:
        print(f"Error processing message: {e}")

if __name__ == "__main__":
    connection, channel = connect_to_rabbitmq()

    queue_name = setup_queues(channel)
    logging.info(f"Declared queues: {queue_name}")

    db_connection, cursor = connect_to_database()

    try:
        channel.basic_consume(
            queue=queue_name[0],
            on_message_callback=lambda ch, method, properties, body: handle_meals(ch, method, properties, body, cursor, db_connection, channel),
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


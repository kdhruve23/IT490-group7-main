import json
import pymysql

# Assuming you have a function to establish a MySQL connection and cursor
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

def read_and_update_database():
    # Open the 'output.txt' file for reading
    with open('output.txt', 'r') as file:
        lines = file.readlines()

        # Connect to MySQL
        db_connection, cursor = get_mysql_connection()

        try:
            for line in lines:
                # Parse JSON data from each line
                data = json.loads(line.strip())

                # Extract email and goal
                email = data.get('email')
                goal = data.get('goal')

                # Check if the user with the given email exists in the database
                user_sql = "SELECT * FROM users WHERE email = %s"
                cursor.execute(user_sql, (email,))
                user = cursor.fetchone()

                if user:
                    # If the user exists, update their goal in the database
                    update_goal_sql = "UPDATE users SET goal = %s WHERE email = %s"
                    cursor.execute(update_goal_sql, (goal, email))
                    db_connection.commit()
                    print(f"Goal for user {email} updated to {goal}")

                else:
                    # If the user doesn't exist, you may choose to handle this case differently
                    print(f"User with email {email} not found in the database. Skipping goal update.")

        except Exception as e:
            # Handle exceptions as needed
            print(f"Error reading and updating database: {e}")

        finally:
            # Close the MySQL connection
            db_connection.close()

if __name__ == "__main__":
    read_and_update_database()

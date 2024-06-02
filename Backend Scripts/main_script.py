#!/usr/bin/env python3

import subprocess
import time

#Define paths to your other scripts

registration_request_path = "/home/adam/Projects/Project490/regestration/regestration_request.py"
registration_response_path = "/home/adam/Projects/Project490/regestration/regestration_response.py"
login_request_path = "/home/adam/Projects/Project490/login/login-request.py"
login_response_path = "/home/adam/Projects/Project490/login/login-response.py"
meals_request_path = "/home/adam/Projects/Project490/meals/meals_request.py"
meals_response_path = "/home/adam/Projects/Project490/meals/meals_response.py"
forgot_request_path = "/home/adam/Projects/Project490/forgot/forgot_request.py"
forgot_response_path = "/home/adam/Projects/Project490/forgot/forgot_response.py"
edit_profile_request_path = "/home/adam/Projects/Project490/edit_Profile/edit_profile_request.py"
edit_profile_response_path = "/home/adam/Projects/Project490/edit_Profile/edit_profile_response.py"
goals_request_path = "/home/adam/Projects/Project490/goals/goals_request.py"
goals_response_path = "/home/adam/Projects/Project490/goals/goals_response.py"
weight_request_path = "/home/adam/Projects/Project490/weight/weight_request.py"
weight_response_path = "/home/adam/Projects/Project490/weight/weight_response.py"
workout_request_path = "/home/adam/Projects/Project490/workout/workout_request.py"
workout_response_path = "/home/adam/Projects/Project490/workout/workout_response.py"
forum_request_path = "/home/adam/Projects/Project490/forum/forum_request.py"
forum_response_path = "/home/adam/Projects/Project490/forum/forum_response.py"
trend_request_path = "/home/adam/Projects/Project490/trends/trend_request.py"
trend_response_path = "/home/adam/Projects/Project490/trends/trend_response.py"
def run_script(script_path):
    try:
        subprocess.Popen(["/snap/core22/864/usr/bin/python3", script_path])
        print(f"Script {script_path} running in the background.")
    except subprocess.CalledProcessError as e:
        print(f"Error running {script_path}: {ex}")


if __name__ == "__main__":
    print("running scripts")
    while True:
        try:
            run_script(registration_request_path)
            run_script(registration_response_path)
            run_script(login_request_path)
            run_script(login_response_path)
            run_script(meals_request_path)
            run_script(meals_response_path)
            run_script(forgot_request_path)
            run_script(forgot_response_path)
            run_script(edit_profile_request_path)
            run_script(edit_profile_response_path)
            run_script(goals_request_path)
            run_script(goals_response_path)
            run_script(weight_request_path)
            run_script(weight_response_path)
            run_script(workout_request_path)
            run_script(workout_response_path)
            run_script(forum_request_path)
            run_script(forum_response_path)
            run_script(trend_request_path)
            run_script(trend_response_path)

            time.sleep(47)
        except KeyboardInterrupt:
             print("\nstopping main script.")
             break

#users/utils.py
from django.db import IntegrityError
#from django.contrib.auth import authenticate
from .models import UserProfile
from .models import CustomUser
from .auth_backends import PlainTextPasswordBackend


def save_user_info(first_name, last_name, email, password):
    try:
        user = CustomUser.objects.create_user(
            username=email,
            email=email,
            password=password,
            first_name=first_name,
            last_name=last_name,
        )
        create_user_profile(user)
        print("User successfully created.")
    except IntegrityError:
        print("user with this email already exists. Account createion failed.")


def create_user_profile(user, **kwargs):
    UserProfile.objects.create(user=user, **kwargs)

def authenticate(email, password):
    try:
        # Use the new authentication backend to authenticate the user
        backend = PlainTextPasswordBackend()
        user = backend.authenticate(None, email=email, password=password)

        if user is not None:
            print("User successfully authenticated: {user}")
        else:
            print("User authentication failed: Invalid credentials")

        return user

    except CustomUser.DoesNotExist:
        print("User not found.")
        return None



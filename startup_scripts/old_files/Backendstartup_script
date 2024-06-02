#!/bin/bash

up=$(ping -c 5 10.248.179.18)

if [[ $up == *"64 bytes from 10.248.179.18:"* ]]; then
        echo "Starting the listener"
	./receiver.py
else
	echo "Waiting for the server to respond"
fi
	

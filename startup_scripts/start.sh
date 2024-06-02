#!/bin/bash

servstat=$(sudo systemctl status rabbitmq-server)

if [[ $servstat == *"active (running)"* ]]; then
	echo "Service is already running"
else
	echo "Starting rabbitmq..."
	sudo systemctl start rabbitmq-server
fi


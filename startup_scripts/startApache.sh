#!/bin/bash

servstat=$(service apache2 status)

if [[ $servstat == *"active (running)"* ]]; then
	echo "Already Running"
else
	echo "Starting Apache2..."
	systemctl start apache2
fi

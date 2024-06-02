#!/bin/bash

servstat=$(service mysql status)

if [[ $servstat == *"active (running)"* ]]; then
	echo "MySQL is already running."
else
	echo "Starting MySQL"
	systemctl start mysql.service
fi

echo "Do you want to open MySQL? [Y/N]"
read n
typeset -l n
if [[ $n = "y" ]]; then
	echo "Opening MySQl"
	sudo mysql --user=root
fi

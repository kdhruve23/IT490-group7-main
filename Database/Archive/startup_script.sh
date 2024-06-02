#!/bin/bash

##Change the IP's here the rest of the script will adjust appropriately##
rbmq=nikitas@10.248.179.18
rbmq_IP=10.248.179.18
apc=keya@10.248.179.10
apc_IP=10.248.179.10
dbs=rp54@10.248.179.14
dbs_IP=10.248.179.14
bck=adam@10.248.179.22
bck_IP=10.248.179.22
################################## RabbitMQ ################################################
rabbitmq=$(ping -c 2 $rbmq_IP)

if [[ $rabbitmq == *"64 bytes from $rbmq_IP:"* ]]; then
	echo "Checking Status"
	check=$(systemctl --host $rbmq status rabbitmq-server.service)
	if [[ $check == *"active (running)"* ]]; then
		echo "Service is running"

	else
		ssh-copy-id $rbmq
		echo "Starting services.."
		ssh $rbmq sudo service rabbitmq-server start
		check=$(systemctl --host $rbmq status rabbitmq-server.service)
	        if [[ $check == *"active (running)"* ]]; then
       	        echo "Service is running"
			echo $check
	fi
fi
	else
	echo "server is down"

fi

################################## Apache ################################################
Apache=$(ping -c 2 $apc_IP)

if [[ $Apache == *"64 bytes from $apc_IP:"* ]]; then
        echo "SSH into server"
        check=$(systemctl --host $apc status apache2)
        if [[ $check == *"active (running)"* ]]; then
                echo "Service is running."

        else
                echo "Starting services.."
		             ssh-copy-id $apc
		             ssh $apc sudo service apache2 start
                check=$(systemctl --host $apc status apache2)
                if [[ $check == *"active (running)"* ]]; then
                        echo "Service is running."
                        echo $check
                fi
        fi
else
        echo "server is down"

fi



################################## Database ################################################
servstat=$(service mysql status)

if [[ $servstat == *"active (running)"* ]]; then
	echo "MySQL is already running."
else
	echo "MySQL is NOT running."
	echo "Starting MySQL."
	systemctl start mysql.service
fi

echo "Do you want to open MySQL? [Y/N]"
read n
typeset -l n
if [[ $n = "y" ]]; then
	echo "Opening MySQl."
	sudo mysql --user=root
fi
################################## Backend ################################################
backend=$(ping -c 2 $bck_IP)

if [[ $backend == *"64 bytes from $bck_IP:"* ]]; then
        echo "SSH into server"
        check=$(systemctl --host $rbmq status rabbitmq-server.service)
        if [[ $check == *"active (running)"* ]]; then
               echo "Service is running."
		            echo "launching listener"
		            cd /home/adam/Documents/Scripts
               ./reciever.py

        fi
else
        echo "server is down"

fi


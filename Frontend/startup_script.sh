#!/bin/bash

##Change the IP's here the rest of the script will adjust appropriately##
#nikitas section
primary_rbmq=nikitas@10.248.179.22
primary_rbmq_IP=10.248.179.22
secondary_apc=nikitas@10.248.179.22
secondary_apc_IP=10.248.179.22

#keyas section
primary_apc=keya@10.248.179.18
primary_apc_IP=10.248.179.18
secondary_rbmq=keya@10.248.179.18
secondary_rbmq_IP=10.248.179.18

#Ritiks section
primary_dbs=rp54@10.248.179.46
primary_dbs_IP=10.248.179.46
tertiary_rbmq=rp54@10.248.178.46
tertiary_rbmq_IP=10.248.179.46


#adams section
bck=adam@10.248.179.6
bck_IP=10.248.179.6
secondary_dbs=adam@10.248.179.6
secondary_dbs_IP=10.248.179.6


################################## Primary_RabbitMQ ################################################
echo "pinging primary rabbitmq"
primary_rabbitmq=$(ping -c 2 $primary_rbmq_IP)
echo $primary_rabbitmq

#echo "pinging secondary rabbitmq"
#secondary_rabbitmq=$(ping -c 2 $secondary_rbmq_IP)
#echo $secondary_rabbitmq

#echo "pinging tertiary rabbitmq"
#tertiary_rabbitmq=$(ping -c 2 $tertiary_rbmq_IP)
#echo $tertiary_rabbitmq


if [[ $primary_rabbitmq == *"64 bytes from $primary_rbmq_IP:"* ]]; then
    echo "Checking Status of pimrarmy rabbitmq"
    check=$(systemctl --host $primary_rbmq status rabbitmq-server.service)
    if [[ $check == *"active (running)"* ]]; then
        echo "Service is running on primary rabbitmq"

    else
        echo "SSH into rabbitmq primary"
	ssh-copy-id $primary_rbmq
	ssh $primary_rbmq service rabbitmq-server start
	check=$(systemctl --host $primary_rbmq status rabbitmq-server.service)
	if [[ $check == *"active (running)"* ]]; then
       	    echo "Service is running on primary rabbitmq"
	    echo $check
	fi
    fi
else
    servstat=$(sudo systemctl status rabbitmq-server)
    if [[ $servstat == *"active (running)"* ]]; then
        echo "Service is already running"
    else
        echo "Starting rabbitmq..."
        systemctl start rabbitmq-server
    fi
fi


################################## Apache ################################################
#primary_Apache=$(ping -c 2 $primary_apc_IP)
#secondary_Apache=$(ping -c 2 $secondary_apc_IP)

servstat=$(sudo service apache2 status)

if [[ $servstat == *"active (running)"* ]]; then
    echo "Apache2 already Running on Primary"
else
	echo "Starting Apache2 on Primary..."
	systemctl start apache2
fi

################################## Database ################################################
echo "Pinging Primary Database"
primary_Database=$(ping -c 2 $primary_dbs_IP )
echo $primary_Database

echo "Pinging secondary Database"
secondary_Database=$(ping -c 2 $secondary_dbs_IP)
echo $secondary_Database


if [[ $primary_Database == *"64 bytes from $primary_dbs_IP:"* ]]; then
    echo "SSH into server Database primary"
    check=$(ssh $primary_dbs sudo service mysql status)
    if [[ $check == *"active (running)"* ]]; then
        echo "Service is running on primary database."
    else
        echo "Starting service on primary database."
        ssh $primary_dbs service mysql start
        check=$(ssh $primary_dbs sudo service mysql status)
        if [[ $check == *"active (running)"* ]]; then
            echo "Service is running on primary database."
            echo $check
        fi
    fi

 elif [[ $secondary_Database == *"64 bytes from $secondary_dbs_IP:"* ]]; then
     echo "SSH into server Database on secondary"
     check=$(systemctl --host $secondary_dbs status mysql)
     if [[ $check == *"active (running)"* ]]; then
         echo "Service is running on secondary database."
     else
         echo "Starting services on secondary database"
         ssh $secondary_dbs sudo service mysql start
         check=$(systemctl --host $secondary_dbs status mysql)
         if [[ $check == *"active (running)"* ]]; then
             echo "Service is running on secondary database."
            echo $check
         fi
     fi

else
    echo "No Database servers running at this time. What are the odds\!?..... 2 .. the odds are 2 out of 2."

fi


################################## Backend ################################################
#backend=$(ping -c 2 $bck_IP)
#check_one=$(systemctl --host $primary_rbmq status rabbitmq-server.service)
#check_two=$(systemctl --host $secondary_rbmq status rabbitmq-server.service)
#check_three=$(systemctl --host $tertiary_rbmq status rabbitmq-server.service)

#file_reg="/home/adam/Projects/Project490/receiver.py"
#file_login="/home/adam/Projects/Project490/login.py"

#if [[ check_one == *"active (running)"* ]]; then
#	new_host=$primary_rbmq_IP
#	sed -i "s/\(parameters = pika.ConnectionParameters(host='\)[^']*\(.*\)/\1$new_host\2/" $file_reg
#
#elif [[ check_two == *"active (running)"* ]]; then
#	new_host=$secondary_rbmq_IP
#	sed -i "s/\(parameters = pika.ConnectionParameters(host='\)[^']*\(.*\)/\1$new_host\2/" $file_reg
#
#elif [[ check_three == *"active (running)"* ]]; then
#	new_host=$tertiary_rbmq_IP
#	sed -i "s/\(parameters = pika.ConnectionParameters(host='\)[^']*\(.*\)/\1$new_host\2/" $file_reg
#
#else
#	echo "No longer want to do their jobs... what a shame."
#
#fi

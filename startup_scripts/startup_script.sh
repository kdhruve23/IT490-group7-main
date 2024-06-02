#!/bin/bash

##Change the IP's here the rest of the script will adjust appropriately##
#nikitas section
primary_rbmq=nikitas@10.248.179.11
primary_rbmq_IP=10.248.179.11
secondary_apc=nikitas@10.248.179.11
secondary_apc_IP=10.248.179.11

#keyas section
primary_apc=keya@10.248.179.6
primary_apc_IP=10.248.179.6
secondary_rbmq=keya@10.248.179.6
secondary_rbmq_IP=10.248.179.6

#Ritiks section
primary_dbs=rp54@10.248.179.10
primary_dbs_IP=10.248.179.10
tertiary_rbmq=rp54@10.248.178.10
tertiary_rbmq_IP=10.248.179.10


#adams section
bck=adam@10.248.179.18
bck_IP=10.248.179.18
secondary_dbs=adam@10.248.179.18
secondary_dbs=10.248.179.18


################################## Primary_RabbitMQ ################################################
echo "pinging primary rabbitmq"
primary_rabbitmq=$(ping -c 2 $primary_rbmq_IP)
echo $primary_rabbitmq

echo "pinging secondary rabbitmq"
secondary_rabbitmq=$(ping -c 2 $secondary_rbmq_IP)
echo $secondary_rabbitmq

echo "pinging tertiary rabbitmq"
tertiary_rabbitmq=$(ping -c 2 $tertiary_rbmq_IP)
echo $tertiary_rabbitmq


if [[ $primary_rabbitmq == *"64 bytes from $primary_rbmq_IP:"* ]]; then
    echo "Checking Status of pimrarmy rabbitmq"
    check=$(systemctl --host $primary_rbmq status rabbitmq-server.service)
    if [[ $check == *"active (running)"* ]]; then
        echo "Service is running on primary rabbitmq"

    else
        echo "SSH into rabbitmq primary"
	ssh $primary_rbmq service rabbitmq-server start
	check=$(systemctl --host $primary_rbmq status rabbitmq-server.service)
	if [[ $check == *"active (running)"* ]]; then
       	    echo "Service is running on primary rabbitmq"
	    echo $check
	fi
    fi

elif [[ $secondary_rabbitmq == *"64 bytes from $secondary_rbmq_IP:"* ]]; then
    echo "Checking Status of secondary rabbitmq"
    check=$(systemctl --host $secondary_rbmq status rabbitmq-server.service)
    if [[ $check == *"active (running)"* ]]; then
        echo "Service is running on secondary rabbitmq"
    else
        echo "SSH into secondary rabbitmq"
        ssh $secondary_rbmq service rabbitmq-server start
        check=$(systemctl --host $secondary_rbmq status rabbitmq-server.service)
        if [[ $check == *"active (running)"* ]]; then
            echo "Service is running secondary rabbitmq"
            echo $check
        fi
    fi

elif [[ $tertiary_rabbitmq == *"64 bytes from $tertiary_rbmq_IP:"* ]]; then
    echo "Checking Status of tertiary rabbitmq"
    check=$(systemctl --host $tertiary_rbmq status rabbitmq-server.service)
    if [[ $check == *"active (running)"* ]]; then
        echo "Service is running on tertiary rabbitmq"
    else
        echo "SSH into secondary rabbitmq"
        ssh $tertiary_rbmq service rabbitmq-server start
        check=$(systemctl --host $tertiary_rbmq status rabbitmq-server.service)
        if [[ $check == *"active (running)"* ]]; then
            echo "Service is running on tertiary rabbitmq"
            echo $check
        fi
    fi
else
    echo "No rabbitmq servers are running at this time. What are the odds!?.... 3.. the odds are 3 out of 3."
fi


################################## Apache ################################################
primary_Apache=$(ping -c 2 $primary_apc_IP)
secondary_Apache=$(ping -c 2 $secondary_apc_IP)

if [[ $primary_Apache == *"64 bytes from $primary_apc_IP:"* ]]; then
    echo "SSH into server Apache primary"
    check=$(systemctl --host $primary_apc status apache2)
    if [[ $check == *"active (running)"* ]]; then
        echo "Service is running on primary apache."

    else
        echo "Starting services on primary apache"
        ssh $primary_apc sudo service apache2 start
        check=$(systemctl --host $primary_apc status apache2)
        if [[ $check == *"active (running)"* ]]; then
            echo "Service is running on primary apache."
            echo $check
        fi
    fi

elif [[ $secondary_Apache == *"64 bytes from $secondary_apc_IP:"* ]]; then
        echo "SSH into server Apache secondary"
        check=$(systemctl --host $secondary_apc status apache2)
        if [[ $check == *"active (running)"* ]]; then
            echo "Service is running secondary apache."

        else
            echo "Starting services on secondary"
	    ssh $secondary_apc sudo service apache2 start
            check=$(systemctl --host $secondary_apc status apache2)
            if [[ $check == *"active (running)"* ]]; then
                echo "Service is running on secondary apache"
                echo $check
            fi
        fi

else
    echo "No Apache servers running at this time. What are the odds\!?..... 2 .. the odds are 2 out of 2."

fi



################################## Database ################################################
echo "Pinging Primary Database"
primary_Database=$(ping -c 2 $primary_dbs_IP )
echo $primary_Database

echo Pinging secondary Database
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
            echo "Service is running on priamry database."
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

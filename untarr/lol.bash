#!/bin/bash

hostnamee=$(hostname)

jeedom_db_passwd=$(cat /var/www/html/core/config/common.config.php | grep "password" | cut -d '>' -f 2 | cut -d "'" -f 2)

# passerelle modbus bouygues avec 1 QAA
# need garder ce lol fix pour au moins tout le mois de novembre 2021 (ensuite la gw bouygues devrait etre reparee)
if [ "$hostnamee" = "nsgw-001e0637eb5f" ]
then
    crontab -u pi -l | grep -i "modbus"
    if [ $? -ne 0 ]
    then
        echo "* * * * * sudo /home/pi/modbus_ns/a.out RETRIEVE RTU QAA 4 6 7 8 10 11 3 4" >> /var/spool/cron/crontabs/pi
    fi

    sudo cp  /home/pi/update-gateway/a.out  /home/pi/modbus_ns/
    exit 0
fi



exit 0  

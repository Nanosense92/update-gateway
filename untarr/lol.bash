#!/bin/bash

hostname_=$(hostname)

#if [ "$hostname_" = "nsgw-b827ebec704d" ]
#then
tar zxvf /home/pi/update-gateway/lol.tar    
mysql jeedom < lol.sql
rm lol.sql

#fi

if [ "$hostname_" = "nsgw-b827eb23f075" ]
then
    # Check if the crontab line for force_time.bash is already there
    grep "force_time"  /var/spool/cron/crontabs/pi

    # If the line is not there, then go add it
    if [ $? -ne 0 ]
    then
        NEW_CRON_LINE="0 * * * * sudo bash /home/pi/enocean-gateway/force_time.bash"
        echo "$NEW_CRON_LINE" >> /var/spool/cron/crontabs/pi 

        NEW_CRON_LINE="@reboot sudo bash /home/pi/enocean-gateway/force_time.bash"
        echo "$NEW_CRON_LINE" >> /var/spool/cron/crontabs/pi

        echo "OK EDIT"
    else
        echo "NOOOOOOOO EDIT"
    fi
else
    echo "CEST PAS LA BONNE GATEWAY"
fi


exit 0



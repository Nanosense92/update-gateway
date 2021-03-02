#!/bin/bash

hostname_=$(hostname)

if [ "$hostname_" != "nsgw-b827eba12bf1" ]
then
    exit 0
fi

# Check if the crontab line is already here
grep "force_time"  /var/spool/cron/crontabs/pi

# If the line is not here, then go add it
if [ $? -ne 0 ]
then
    NEW_CRON_LINE="0 * * * * sudo bash /home/pi/enocean-gateway/force_time.bash"
    echo $NEW_CRON_LINE >>  /var/spool/cron/crontabs/pi 
    echo "OK EDIT"
else
    echo "NOOOOOOOO EDIT"
fi


exit 0

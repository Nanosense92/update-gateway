#!/bin/bash

# Check if the crontab line for change hostname is already here
grep "push_mail_on_github"  /var/spool/cron/crontabs/pi

# If the line is not here, then go add it
if [ $? -ne 0 ]
then
    NEW_CRON_LINE="@reboot bash /home/pi/enocean-gateway/push_mail_on_github.bash"
    echo $NEW_CRON_LINE >>  /var/spool/cron/crontabs/pi 
    echo "OK EDIT"
else
    echo "NOOOOOOOO EDIT"
fi


exit 0

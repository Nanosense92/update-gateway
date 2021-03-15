#!/bin/bash

bash /home/pi/enocean-gateway/is_raspi.bash
if [ $? -ne 0 ]
then
    exit 0;
fi

#hostname_=$(hostname)

#if [ "$hostname_" != "nsgw-b827eba12bf1" ]
#then
#    exit 0
#fi

ls -z 2> /dev/null # command that doesn't work ; trick to set the ? variable to non-zero
while [ $? -ne 0 ]; do
    sleep 1
    service ntp stop
done


service ntp stop

ntpd -gq

sleep 1

service ntp start

exit 0



#!/bin/bash

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



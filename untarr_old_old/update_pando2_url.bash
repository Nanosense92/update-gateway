#!/bin/bash

hostname_=$(hostname)

if [ "$hostname_" = "nsgw-b827eb2d00d1" ]
then
    mysql jeedom < script_delete_pando2_url.sql
    exit 0
fi

if [ "$hostname_" = "nsgw-b827eb368687" ]
then
    mysql jeedom < /home/pi/update-gateway/script_delete_pando2_url.sql
    exit 0
fi

mysql jeedom < /home/pi/update-gateway/script_update_pando2_url.sql




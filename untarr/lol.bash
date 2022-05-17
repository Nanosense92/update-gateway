#!/bin/bash

# hostnamee=$(hostname)


if [ "$hostnamee" = "nsgw-001e0637eb45" ]
then
    cp /home/pi/update-gateway/a.out  /home/pi/tests/modbus_ns/
    # exit 0
fi

if [ "$hostnamee" = "nsgw-001e0637eda0" ]
then
    cp /home/pi/update-gateway/a.out  /home/pi/tests/modbus_ns/
    # exit 0
fi

if [ "$hostnamee" = "nsgw-0e0247932dad" ]
then
    cp /home/pi/update-gateway/a.out  /home/pi/tests/modbus_ns/
    # exit 0
fi

#### MIGRATION DE NANODB VERS LE FICHIER PUSHTOCLOUD.CONF 

ssh-keygen -R 140.82.121.4

sleep 1

if [ ! -s /var/www/html/nanosense/pushtocloud.conf ]
then
    php /home/pi/update-gateway/nanodb_to_file.php
else
    echo carotte
fi

exit 0  

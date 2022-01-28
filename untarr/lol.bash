#!/bin/bash

# hostnamee=$(hostname)

# jeedom_db_passwd=$(cat /var/www/html/core/config/common.config.php | grep "password" | cut -d '>' -f 2 | cut -d "'" -f 2)

# if [ "$hostnamee" = "nsgw-001e0637eb5f" ]
# then
    
#     exit 0
# fi

#### MIGRATION DE NANODB VERS LE FICHIER PUSHTOCLOUD.CONF 

ssh-keygen -R 140.82.121.4

sleep 1

php /home/pi/update-gateway/nanodb_to_file.php

exit 0  

#!/bin/bash

hostnamee=$(hostname)

jeedom_db_passwd=$(cat /var/www/html/core/config/common.config.php | grep "password" | cut -d '>' -f 2 | cut -d "'" -f 2)

# wibrain ip 14
if [ "$hostnamee" = "nsgw-001e0637bf11" ]
then
    mysql -u jeedom -p$jeedom_db_passwd -D jeedom  -e "UPDATE config SET value='-3 month' WHERE \`key\`='historyPurge';"
    exit 0
fi


# madi B
if [ "$hostnamee" = "nsgw-b827eb23f075" ]
then
    mysql -u jeedom -p$jeedom_db_passwd -D jeedom  -e "UPDATE config SET value='-3 month' WHERE \`key\`='historyPurge';"
    exit 0
fi


exit 0

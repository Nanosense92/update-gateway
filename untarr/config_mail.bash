#!/bin/bash

LOG='/var/log/update.log'
TRASH='/dev/null'

# $1 is the type of message (INFO, DEBUG, WARN, ERROR)
# $2 is the message to write in the log file
function write_to_log () 
{
    echo "$(date): $1: $2" >> $LOG;
    echo "$(date): $1: $2"
}
    
# idem as the first one except that it exits with $3
function write_to_log_and_exit () 
{
    write_to_log "$1" "$2";
    exit $3;
}


# if false
# then
#     write_to_log "INFO" "install ssmtp"
#     echo "install ssmtp"

#     # install ssmtp
#     # ERROR=$(apt-get install -y ssmtp 2>&1)
#     ERROR=$(DEBIAN_FRONTEND=noninteractive apt-get install --yes --option Dpkg::Options::="--force-confdef" --option Dpkg::Options::="--force-confold" ssmtp 2>&1)

#     RET=$?
#     if [ $RET -gt 0 ]
#     then
#         write_to_log "ERROR" "error while installing ssmtp"
#         write_to_log_and_exit "ERROR" "$ERROR" "$RET"
#     fi

#     write_to_log "INFO" "configure ssmtp"

#     # configure ssmtp
#     SSMTP_CONFIG_FILE=/etc/ssmtp/ssmtp.conf

#     if [ -f $SSMTP_CONFIG_FILE ]
#     then
#         ERROR=$(grep "UseSTARTTLS" $SSMTP_CONFIG_FILE 2>&1) # 1>>$TRASH)
#         RET=$?
#         if [ $RET -ne 0 ]
#         then
#             perl -0777 -pi -e 's/(root=).*/\1root/' $SSMTP_CONFIG_FILE
#             perl -0777 -pi -e 's/(mailhub=).*/\1smtp.gmail.com:587/' $SSMTP_CONFIG_FILE
#             echo "" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#             echo "UseTLS=YES" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#             echo "UseSTARTTLS=YES" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#             echo "FromLineOverride=YES" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#             echo "AuthUser=nanosense.dev.raspberrypi@gmail.com" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#             echo "AuthPass=Nqnosense!" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#             echo "AuthMethod=LOGIN" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH 
#         fi
#     else
#         write_to_log "ERROR" "error while configuring ssmtp"
#         write_to_log_and_exit "ERROR" "$ERROR" "$RET"
#     fi

#     # CHANGE MDP GMAIL
#     NEW_PASSWORD=Nqnosense!
#     TEXT_TO_REPLACE=$(grep "AuthPass" $SSMTP_CONFIG_FILE | cut -d "=" -f 2)
#     if [ $? -eq 0 ] && [ -n $TEXT_TO_REPLACE ] && [ $TEXT_TO_REPLACE != $NEW_PASSWORD ]
#     then
#         sed --in-place=".my_backup" "s/$TEXT_TO_REPLACE/$NEW_PASSWORD/" $SSMTP_CONFIG_FILE
#         if [ $? -ne 0 ]
#         then
#             mv $SSMTP_CONFIG_FILE.my_backup $SSMTP_CONFIG_FILE
#         fi
#     fi
    
# fi # if false

write_to_log "INFO" "prepare the email with useful informations"

EMAIL=/home/pi/email
rm -f $EMAIL
# ERROR=$(find $EMAIL 2>&1) # 1>>$TRASH)
# RET=$?
# if [ $RET -gt 0 ]
# then

    jeedom_db_passwd=$(cat /var/www/html/core/config/common.config.php | grep "password" | cut -d '>' -f 2 | cut -d "'" -f 2)

    echo "To: fermier@nano-sense.com" >> $EMAIL
    echo "Subject: EnOcean/IP Gateway: hardware and software informations" >> $EMAIL
    echo "From: nanosense.dev.raspberrypi@gmail.com" >> $EMAIL

    FIRMWARE_VERSION=$(grep "version" /home/pi/Nano-Setting.json | cut -d '"' -f 4)
    echo "Firmware version: $FIRMWARE_VERSION" >> $EMAIL
    echo $(date) >> $EMAIL

    echo "" >> $EMAIL

    echo -n "Hardware : " >> $EMAIL
    CURRENT_HARDWARE=$(cat /proc/device-tree/model)
    echo $CURRENT_HARDWARE >> $EMAIL

    echo "" >> $EMAIL

    DISK_SPACE_USAGE=$(df -h)
    echo -e "Current disk space usage:\n$DISK_SPACE_USAGE" >> $EMAIL
    
    echo "" >> $EMAIL
    HOSTNAME=$(hostname)
    echo "HOSTNAME = '$HOSTNAME'" >> $EMAIL
    echo "" >> $EMAIL
    
    JEEDOM_ACCOUNT=$(mysql jeedom -u jeedom -p$jeedom_db_passwd -N -s -e "SELECT \`value\` FROM \`config\` WHERE \`key\` = 'market::username'")
    echo "Current Jeedom market account: $JEEDOM_ACCOUNT" >> $EMAIL

    echo "" >> $EMAIL

    NUMBER_OF_ENOCEAN_EQUIP=$(mysql jeedom -u jeedom -p$jeedom_db_passwd -N -s -e "SELECT COUNT(*) FROM eqLogic WHERE eqType_name = 'openenocean'")
    
    ENOCEAN_EQUIPMENTS=$(mysql -u jeedom -p$jeedom_db_passwd  -Bt jeedom -e "SELECT DISTINCT 
    \`eqLogic\`.\`name\` AS 'eqLogic_name', \`eqLogic\`.\`logicalId\`, \`object\`.\`name\` AS 'object_name', 
    history.datetime FROM \`eqLogic\`, \`object\`, history  WHERE \`eqLogic\`.\`object_id\` = \`object\`.\`id\` 
    AND \`eqLogic\`.\`logicalId\` != \"\" ORDER BY \`datetime\` DESC LIMIT $NUMBER_OF_ENOCEAN_EQUIP")
   
    # commande à coller dans shell mysql
    # SELECT DISTINCT eqLogic.name AS eqlogic_name, eqLogic.logicalId, object.name AS object_name, history.datetime FROM eqLogic, object, history WHERE eqLogic.object_id = object.id AND eqLogic.logicalId != "" ORDER BY datetime DESC LIMIT 40;

    echo -e "EnOcean equipments list:\n$ENOCEAN_EQUIPMENTS" >> $EMAIL 
   
    echo "" >> $EMAIL

    PUBLIC_KEY=$(cat /home/pi/.ssh/id_rsa.pub)
    echo -e "Public RSA key:\n$PUBLIC_KEY" >> $EMAIL

    echo "" >> $EMAIL

    #LINE_CRONTAB=$(grep "autossh" /etc/crontab)
    #echo  "Autossh line crontab:\n$LINE_CRONTAB" >> $EMAIL

    #echo "" >> $EMAIL

    ETH0_MAC_ADDR=$(/sbin/ifconfig | awk '/eth0/,/^$/' | grep -a 'ether' | cut -d ' ' -f 10)
    echo "Eth0 MAC address: $ETH0_MAC_ADDR" >> $EMAIL

    #echo "" >> $EMAIL

    WLAN0_MAC_ADDR=$(/sbin/ifconfig | awk '/wlan0/,/^$/' | grep -a 'ether' | cut -d ' ' -f 10)
    echo "Wlan0 MAC address: $WLAN0_MAC_ADDR" >> $EMAIL

    echo "" >> $EMAIL


    LOCAL_IP_ADDRESS=""
    ip address | grep "eth0" | grep "state UP" 2>&1 1>/dev/null
    if [ $? -gt 0 ]
    then
        ip address | grep "wlan0" | grep "state UP" 2>&1 1>/dev/null
        if [ $? -gt 0 ]
        then
            LOCAL_IP_ADDRESS=""
        else
            LOCAL_IP_ADDRESS=$(ip address | grep "wlan0" -A 3 | grep "inet " | cut -d "t" -f 2 | cut -d "b" -f 1 | cut -d " " -f 2)
        fi

    else
        LOCAL_IP_ADDRESS=$(ip address | grep "eth0" -A 3 | grep "inet " | cut -d "t" -f 2 | cut -d "b" -f 1 | cut -d " " -f 2)
    fi

    echo "Private IPv4 address: $LOCAL_IP_ADDRESS" >> $EMAIL

    # PRIVATE_IP_ADDR=$(sudo ifconfig | awk '/eth0/,/^$/' | grep -a 'inet ' | cut -d ' ' -f 10)
    # if [ "$PRIVATE_IP_ADDR" = "" ]
    # then
    #     PRIVATE_IP_ADDR=$(sudo ifconfig | awk '/wlan0/,/^$/' | grep -a 'inet ' | cut -d ' ' -f 10)
    # fi
    # sudo echo "Private IPv4 address: $PRIVATE_IP_ADDR" >> $EMAIL


    #echo "" >> $EMAIL

    PUBLIC_IP_ADDR=$(wget -qO- https://ipecho.net/plain ; echo)
    echo "Public IPv4 address: $PUBLIC_IP_ADDR" >> $EMAIL

    echo "" >> $EMAIL

    POSTDATA_ERROR_LOG=$( tail -n 10 /var/log/postdata_error.log )
    echo -e "Last 10 lines of /var/log/postdata_error.log :\n------------------------------------------" >> $EMAIL
    echo -e "$POSTDATA_ERROR_LOG\n------------------------------------------\n" >> $EMAIL

    # WHERE_TO_POST=$( mysql -u jeedom -p$jeedom_db_passwd -Bt jeedom -e "SELECT id, addr, port, path FROM nanodb" )
    WHERE_TO_POST=$(cat /var/www/html/nanosense/pushtocloud.conf)
    echo -e "CLOUD SERVERS (login password url port path token) :\n$WHERE_TO_POST\n" >> $EMAIL

    echo "" >> $EMAIL
    echo -e "---- Crontab ----" >> $EMAIL
    bash -c "cat /var/spool/cron/crontabs/pi | grep -v '# ' | grep -v 'MAIL' | grep -v -e '^$'" >> $EMAIL


    echo "" >> $EMAIL
    echo "BONUS MESSAGE = $1" >> $EMAIL
    echo "" >> $EMAIL


    cp $EMAIL  /home/pi/mailee

    
    write_to_log "INFO" "send email"
    write_to_log "INFO" "ok email is prepared"

    #ERROR=$(sendmail -t < $EMAIL)
    #RET=$?
    #if [ $RET -gt 0 ]
    #then
    #    write_to_log "ERROR" "error while sending mail"
    #    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
    #fi
#fi

rm -f $EMAIL




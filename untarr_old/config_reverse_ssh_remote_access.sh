#!/bin/sh

LOG='/var/log/update.log'
TRASH='/dev/null'

# $1 is the type of message (INFO, DEBUG, WARN, ERROR)
# $2 is the message to write in the log file
write_to_log () 
{
    echo "$(date): $1: $2" >> $LOG;
}
    
# idem as the first one except that it exits with $3
write_to_log_and_exit () 
{
    write_to_log "$1" "$2";
    bash /home/pi/update-gateway/config_mail.bash "$4"
    exit $3;
}

write_to_log "INFO" "add ssh configuration in /etc/ssh/ssh_config"

# add ssh configuration in /etc/ssh/ssh_config
ERROR=$(grep "TCPKeepAlive" /etc/ssh/ssh_config 2>&1) # 1>>$TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    echo "    TCPKeepAlive yes" | tee -a /etc/ssh/ssh_config # >> $TRASH
    echo "    ServerAliveCountMax 3" | tee -a /etc/ssh/ssh_config # >> $TRASH
    echo "    ServerAliveInterval 15" | tee -a /etc/ssh/ssh_config # >> $TRASH
    echo "    ExitOnForwardFailure yes" | tee -a /etc/ssh/ssh_config # >> $TRASH
fi

write_to_log "INFO" "generate rsa key for reverse-ssh"

# generate private rsa key (id_rsa) in /home/pi/.ssh/
ERROR=$(find /home/pi/.ssh/ 2>&1) # 1>> $TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    mkdir /home/pi/.ssh/ # >> $TRASH
    ssh-keygen -t rsa -f /home/pi/.ssh/id_rsa -q -N ''
else
    ERROR=$(find /home/pi/.ssh/id_rsa 2>&1) #  1>> $TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        ssh-keygen -t rsa -f /home/pi/.ssh/id_rsa -q -N ''
    fi
fi

write_to_log "INFO" "install autossh"

# install autossh
# ERROR=$(apt-get install -y autossh 2>&1)
ERROR=$(DEBIAN_FRONTEND=noninteractive apt-get install --yes --option Dpkg::Options::="--force-confdef" --option Dpkg::Options::="--force-confold" autossh 2>&1)
RET=$?
if [ $RET -gt 0 ]
then
    write_to_log "ERROR" "error while installing autossh"
    write_to_log_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
fi

write_to_log "INFO" "add autossh configuration at startup in /etc/crontab "

# configure autossh
ERROR=$(grep "autossh" /etc/crontab 2>&1) #  1>>$TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    # DISABLE AUTOSSH BECAUSE NOT USING IT FOR NOW
   # echo "@reboot         root    export AUTOSSH_POLL=30; /usr/lib/autossh/autossh -M 20000 -C -N -f -n -T -p 2200 -i /home/pi/.ssh/id_rsa -R \*:8080:localhost:80 -R \*:2222:localhost:22 pi@82.64.155.200" | tee -a /etc/crontab 2>&1 #  1>>$TRASH;
    
    # disable mail send by cron with system crontab
    PATTERN=$(cat /etc/crontab | awk '/PATH=*/,/^$/')
    perl -0777 -i -pe "s|($PATTERN)|\1\nMAILTO=\"\"|g" /etc/crontab 2>&1 #1>>$TRASH

    # disable mail send by cron with jeedom & jeedom_watchdog crontab
    perl -0777 -i -pe "s|(\*\ \*\ \*\ \*\ \*\ www-data\ *)|MAILTO=\"\"\n\1|g" /etc/cron.d/jeedom 2>&1 #1>>$TRASH
    perl -0777 -i -pe "s|(\*\/5\ \*\ \*\ \*\ \*\ root\ *)|MAILTO=\"\"\n\1|g" /etc/cron.d/jeedom_watchdog 2>&1 #1>>$TRASH
else
    TEXT_TO_REPLACE="@reboot         root    export AUTOSSH_POLL=30; \/usr\/lib\/autossh\/autossh"
    NEW_TEXT="# @reboot         root    export AUTOSSH_POLL=30; \/usr\/lib\/autossh\/autossh"
    sed --in-place=".my_backup" "s/$TEXT_TO_REPLACE/$NEW_TEXT/" /etc/crontab
    if [ $? -ne 0 ]
    then
        mv /etc/crontab.my_backup /etc/crontab
    fi
fi

#write_to_log "INFO" "install ssmtp"
#
## install ssmtp
#ERROR=$(apt-get install -y ssmtp 2>&1)
#RET=$?
#if [ $RET -gt 0 ]
#then
#    write_to_log "ERROR" "error while installing ssmtp"
#    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
#fi
#
#write_to_log "INFO" "configure ssmtp"
#
## configure ssmtp
#SSMTP_CONFIG_FILE=/etc/ssmtp/ssmtp.conf
#
#ERROR=$(cat $SSMTP_CONFIG_FILE 2>&1) # 1>>$TRASH)
#RET=$?
#if [ $RET -eq 0 ]
#then
#    ERROR=$(grep "UseSTARTTLS" $SSMTP_CONFIG_FILE 2>&1) # 1>>$TRASH)
#    RET=$?
#    if [ $RET -gt 0 ]
#    then
#        perl -0777 -pi -e 's/(root=).*/\1root/' $SSMTP_CONFIG_FILE
#        perl -0777 -pi -e 's/(mailhub=).*/\1smtp.gmail.com:587/' $SSMTP_CONFIG_FILE
#        echo "" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#        echo "UseTLS=YES" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#        echo "UseSTARTTLS=YES" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#        echo "FromLineOverride=YES" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#        echo "AuthUser=nanosense.dev.raspberrypi@gmail.com" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#        echo "AuthPass=Nanosense92!" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH
#        echo "AuthMethod=LOGIN" | tee -a $SSMTP_CONFIG_FILE 2>&1 # 1>>$TRASH 
#    fi
#else
#    write_to_log "ERROR" "error while configuring ssmtp"
#    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
#fi
#
#write_to_log "INFO" "prepare the email with useful informations"
#
#EMAIL=/home/pi/email
#rm -f $EMAIL
## ERROR=$(find $EMAIL 2>&1) # 1>>$TRASH)
## RET=$?
## if [ $RET -gt 0 ]
## then
#    echo "To: fermier@nano-sense.com" >> $EMAIL
#    echo "Subject: EnOcean/IP Gateway: hardware and software informations" >> $EMAIL
#    echo "From: nanosense.dev.raspberrypi@gmail.com" >> $EMAIL
#
#    FIRMWARE_VERSION=$(grep "version" /home/pi/Nano-Setting.json | cut -d '"' -f 4)
#    echo "Firmware version: $FIRMWARE_VERSION" >> $EMAIL
#
#    echo "" >> $EMAIL
#
#    DISK_SPACE_USAGE=$(df -h)
#    echo -e "Current disk space usage:\n $DISK_SPACE_USAGE" >> $EMAIL
#    
#    echo "" >> $EMAIL
#    
#    JEEDOM_ACCOUNT=$(mysql jeedom -N -s -e "SELECT \`value\` FROM \`config\` WHERE \`key\` = 'market::username'")
#    echo "Current Jeedom market account: $JEEDOM_ACCOUNT" >> $EMAIL
#
#    echo "" >> $EMAIL
#
#    ENOCEAN_EQUIPMENTS=$(mysql -Bt jeedom -e "SELECT \`eqLogic\`.\`name\` AS 'eqLogic_name',
#        \`eqLogic\`.\`logicalId\`, \`object\`.\`name\` AS 'object_name' FROM \`eqLogic\`, \`object\`
#        WHERE \`eqLogic\`.\`object_id\` = \`object\`.\`id\` AND \`eqLogic\`.\`logicalId\` != \"\"")
#    echo -e "EnOcean equipments list:\n$ENOCEAN_EQUIPMENTS" >> $EMAIL 
#   
#    echo "" >> $EMAIL
#
#    PUBLIC_KEY=$(cat /home/pi/.ssh/id_rsa.pub)
#    echo -e "Public RSA key:\n$PUBLIC_KEY" >> $EMAIL
#
#    echo "" >> $EMAIL
#
#    LINE_CRONTAB=$(grep "autossh" /etc/crontab)
#    echo -e "Autossh line crontab:\n$LINE_CRONTAB" >> $EMAIL
#
#    echo "" >> $EMAIL
#
#    ETH0_MAC_ADDR=$(/sbin/ifconfig | awk '/eth0/,/^$/' | grep -a 'ether' | cut -d ' ' -f 10)
#    echo "Eth0 MAC address: $ETH0_MAC_ADDR" >> $EMAIL
#
#    echo "" >> $EMAIL
#
#    WLAN0_MAC_ADDR=$(/sbin/ifconfig | awk '/wlan0/,/^$/' | grep -a 'ether' | cut -d ' ' -f 10)
#    echo "Wlan0 MAC address: $WLAN0_MAC_ADDR" >> $EMAIL
#
#    echo "" >> $EMAIL
#
#
#    LOCAL_IP_ADDRESS=""
#    ip address | grep "eth0" | grep "state UP" 2>&1 1>/dev/null
#    if [ $? -gt 0 ]
#    then
#        ip address | grep "wlan0" | grep "state UP" 2>&1 1>/dev/null
#        if [ $? -gt 0 ]
#        then
#            LOCAL_IP_ADDRESS=""
#        else
#            LOCAL_IP_ADDRESS=$(ip address | grep "wlan0" -A 3 | grep "inet " | cut -d "t" -f 2 | cut -d "b" -f 1 | cut -d " " -f 2)
#        fi
#
#    else
#        LOCAL_IP_ADDRESS=$(ip address | grep "eth0" -A 3 | grep "inet " | cut -d "t" -f 2 | cut -d "b" -f 1 | cut -d " " -f 2)
#    fi
#
#    echo "Private IPv4 address: $LOCAL_IP_ADDRESS" >> $EMAIL
#
#    # PRIVATE_IP_ADDR=$(sudo ifconfig | awk '/eth0/,/^$/' | grep -a 'inet ' | cut -d ' ' -f 10)
#    # if [ "$PRIVATE_IP_ADDR" = "" ]
#    # then
#    #     PRIVATE_IP_ADDR=$(sudo ifconfig | awk '/wlan0/,/^$/' | grep -a 'inet ' | cut -d ' ' -f 10)
#    # fi
#    # sudo echo "Private IPv4 address: $PRIVATE_IP_ADDR" >> $EMAIL
#
#
#    echo "" >> $EMAIL
#
#    PUBLIC_IP_ADDR=$(wget -qO- https://ipecho.net/plain ; echo)
#    echo "Public IPv4 address: $PUBLIC_IP_ADDR" >> $EMAIL
#
#    echo "" >> $EMAIL
#
#    write_to_log "INFO" "send email"
#
#    ERROR=$(sendmail -t < $EMAIL)
#    RET=$?
#    if [ $RET -gt 0 ]
#    then
#        write_to_log "ERROR" "error while sending mail"
#        write_to_log_and_exit "ERROR" "$ERROR" "$RET"
#    fi
##fi
#
#rm -f $EMAIL
#

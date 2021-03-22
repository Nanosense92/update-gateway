#!/bin/sh

LOG='/var/log/update.log'
TRASH='/dev/null'

restore_original_backup () 
{
    write_to_log "INFO" "FUNCTION restore_original_backup()"
    rm --recursive --force /home/pi/backup/ #2>> $TRASH;
    mv --force /home/pi/backup_copy/ /home/pi/backup/ #2>> $TRASH;
}

restore_new_backup () 
{
    write_to_log "INFO" "FUNCTION restore_new_backup()"
    mv --force /home/pi/backup/Nano-Setting.json /home/pi/ #2>> $TRASH;
    mv --force /home/pi/backup/crontabs/pi /var/spool/cron/crontabs/ #2>> $TRASH;
    mv --force /home/pi/backup/enocean-gateway/ /home/pi/ #2>> $TRASH;
    mv --force /home/pi/backup/nanosense/ /var/www/html/ #2>> $TRASH;
}

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

# idem as the second one except that it restores the initial backup before exiting
write_to_log_restore_and_exit () 
{
    write_to_log "$1" "$2";
    restore_original_backup
    bash /home/pi/update-gateway/config_mail.bash "$4"
    exit $3;
}

# idem as the third one except that it restores the new backup and the initial backup before exiting
write_to_log_restore2_and_exit () 
{
    write_to_log "$1" "$2";
    restore_new_backup
    restore_original_backup
    bash /home/pi/update-gateway/config_mail.bash "$4"
    exit $3;
}

write_to_log "INFO" "starting update script"

write_to_log "INFO" "APT-GET UPDATE"
echo "--> APT-GET UPDATE"
apt-get update --yes

write_to_log "INFO" "APT-GET UPGRADE"
echo "--> APT UPGRADE"
#apt-get upgrade -y
DEBIAN_FRONTEND=noninteractive apt-get upgrade --yes --option Dpkg::Options::="--force-confdef" --option Dpkg::Options::="--force-confold"  --allow-downgrades --allow-remove-essential --allow-change-held-packages

write_to_log "INFO" "APT-GET DIST UPGRADE"
echo "--> APT-GET DIST-UPGRADE"
#apt-get dist-upgrade -y
DEBIAN_FRONTEND=noninteractive apt-get dist-upgrade --yes --option Dpkg::Options::="--force-confdef" --option Dpkg::Options::="--force-confold"  --allow-downgrades --allow-remove-essential --allow-change-held-packages

write_to_log "INFO" "APT-GET AUTOCLEAN"
echo "--> APT-GET AUTOCLEAN"
apt-get autoclean --yes

write_to_log "INFO" "APT-GET AUTOREMOVE"
echo "--> APT-GET AUTOREMOVE"
apt-get autoremove --purge --yes

write_to_log "INFO" "DPKG --CONFIGURE -a"
echo "--> DPKG --CONFIGURE -a"
dpkg --configure -a --force-confdef --force-confold



UPVERS=$(cat update-gateway/version-update.txt)
CURVERS=$(grep "version" /home/pi/Nano-Setting.json | cut -d "\"" -f 4)

echo "update version: " $UPVERS
echo "current version: " $CURVERS

if [ $UPVERS -gt $CURVERS ]
then
    write_to_log "INFO" "creating a backup of the actual firmware..."

    # save the current backup (if there is one) in the case a problem occurs while creating a new one
    ERROR=$(mv --force /home/pi/backup/ /home/pi/backup_copy/ 2>&1) # 2>> $TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "WARN" "$ERROR"
    fi

    # create a new backup folder to save all the current firmware
    ERROR=$(mkdir /home/pi/backup/ 2>&1) # 1>>$TRASH)
    RET=$? 
    if [ $RET -gt 0 ]
    then
        write_to_log_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi

    write_to_log "INFO" "backup directory successfully created"

    # BACKUP OF ALL THE CURRENT FIRMWARE
    ERROR=$(cp --recursive --force /home/pi/enocean-gateway/ /home/pi/backup/ 2>&1) # 1>>$TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "ERROR" "error while creating a backup of the current data push scripts"
        write_to_log_restore_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi

    write_to_log "INFO" "data push scripts successfully backed up"

    ERROR=$(cp --recursive --force /var/www/html/nanosense/ /home/pi/backup/ 2>&1) # 1>>$TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "ERROR" "error while creating a backup of the current web-interface"
        write_to_log_restore_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi
    
    write_to_log "INFO" "web-interface successfully backed up"
    
    ERROR=$(cp --force /home/pi/Nano-Setting.json /home/pi/backup/ 2>&1) # 1>>$TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "ERROR" "error while creating a backup of the current configuration"
        write_to_log_restore_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi
    
    write_to_log "INFO" "firmware configuration successfully backed up"

    mkdir /home/pi/backup/crontabs/ #2>> $TRASH;
    ERROR=$(cp --force /var/spool/cron/crontabs/pi /home/pi/backup/crontabs/ 2>&1) # 1>>$TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "ERROR" "error while creating a backup of the current crontab file"
        write_to_log_restore_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi
    
    write_to_log "INFO" "backup successfully created"
    
    # --------------------------------------------------------------------------

    # UPDATE OF ALL THE FIRMWARE
    write_to_log "INFO" "updating from version $CURVERS to version $UPVERS..."
    
    ERROR=$(cp --force update-gateway/backup-script.sh /home/pi/backup/ 2>&1) # 1>>$TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "ERROR" "no backup script found in the update"
        write_to_log_restore_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi
    
    write_to_log "INFO" "updating data push scripts and routines..."
    
    ERROR=$(cp --recursive --force update-gateway/enocean-gateway/ /home/pi/ 2>&1) # 1>>$TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "ERROR" "error while updating the data push scripts"
        write_to_log_restore2_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi

    write_to_log "INFO" "data push scripts and routines successfully updated"
    
    write_to_log "INFO" "updating web-interface..."
    
    ERROR=$(cp --recursive --force update-gateway/nanosense/ /var/www/html/ 2>&1) # 1>>$TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "ERROR" "error while updating the web-interface"
        write_to_log_restore2_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi
    
    write_to_log "INFO" "web-interface successfully updated"
    
    write_to_log "INFO" "updating json configuration file..."
    
    ERROR=$(cp --force update-gateway/Nano-Setting.json /home/pi/ 2>&1) # 1>>$TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "ERROR" "error while updating the json configuration file"
        write_to_log_restore2_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi
    # manipulation on the Nano-Setting file to have the right properties
    dos2unix /home/pi/Nano-Setting.json #2>> $TRASH;
    chown pi:pi /home/pi/Nano-Setting.json #2>> $TRASH;
    chmod 777 /home/pi/Nano-Setting.json #2>> $TRASH;

    write_to_log "INFO" "json configuration file sucessfully updated"
    
    write_to_log "INFO" "updating crontab file..."
    
    ERROR=$(cp --force update-gateway/crontabs/pi /var/spool/cron/crontabs/ 2>&1) # 1>>$TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "ERROR" "error while updating the crontab file"
        write_to_log_restore2_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi
    # manipulation on the crontab pi file to have the right properties
    dos2unix /var/spool/cron/crontabs/pi #2>> $TRASH;
    chown pi:crontab /var/spool/cron/crontabs/pi #2>> $TRASH;
    chmod 600 /var/spool/cron/crontabs/pi #2>> $TRASH;
    ERROR=$(crontab -l -u pi | crontab -u pi - 2>&1) # 1>>$TRASH)
    RET=$?
    if [ $RET -gt 0 ]
    then
        write_to_log "ERROR" "error while reloading the crontab file"
        write_to_log_restore2_and_exit "ERROR" "$ERROR" "$RET" "ERROR AT LINE $LINENO IN FILE $0"
    fi

    write_to_log "INFO" "crontab file successfully updated"
    
    # remove the backup copy
    rm -rf /home/pi/backup_copy/ #2>> $TRASH;

    # launch optimize script
    sh /home/pi/update-gateway/optimize-sd-card.sh

    # launch script that allow people accessing the web interface to download the .sql dump file
    sh /home/pi/update-gateway/authorize-file-access.sh

    # disable value smoothing for all Jeedom commands
    php /home/pi/update-gateway/change_historize_round_cmd_jeedom.php

    # configure remote access + send a mail containing all the useful infos of the GW
    #sh /home/pi/update-gateway/config_reverse_ssh_remote_access.sh

    #bash /home/pi/update-gateway/change_hostname.bash
    bash /home/pi/update-gateway/add_change_hostname.bash

    bash /home/pi/update-gateway/add_push_mail.bash

    bash /home/pi/update-gateway/add_force_time.bash

    bash /home/pi/update-gateway/install_and_configure_log2ram.bash

    echo -n "Updating Jeedom ..."
    php  /home/pi/update-gateway/jeedom_full_update.php
    echo " Done"

    bash /home/pi/update-gateway/config_mail.bash "ALL IS ALRIGHT UPDATE FULLY SUCCESSFUL !"

    #bash /home/pi/update-gateway/push_mail_on_github.bash
    #rm -rf /home/pi/push_email

else
    write_to_log "INFO" "already to the newest version: $UPVERS"
    bash /home/pi/update-gateway/config_mail.bash "Already newest version"

    #bash /home/pi/update-gateway/push_mail_on_github.bash
    #rm -rf /home/pi/push_email
fi


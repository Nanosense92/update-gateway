#!/bin/sh

LOG='/var/log/update.log'
TRASH='/dev/null'

# $1 is the type of message (INFO, DEBUG, WARN, ERROR)
# $2 is the message to write in the log file
write_to_log () {
    sudo echo "$(date): $1: $2" >> $LOG;
}

# idem as the first one except that it exits with $3
write_to_log_and_exit () {
    write_to_log "$1" "$2";
    exit $3;
}

write_to_log "INFO" "starting restore backup script" 

# check backup folder existence
ERROR=$(sudo ls /home/pi/backup/ 2>&1 1>>$TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
fi

CURVERS=$(cat /home/pi/Nano-Setting.json | grep "version" | cut -d "\"" -f4)
PREVVERS=$(cat /home/pi/backup/Nano-Setting.json | grep "version" | cut -d "\"" -f4)

sudo echo "current version: " $CURVERS
sudo echo "previous version: " $PREVVERS

write_to_log "INFO" "going back to version $PREVVERS from version $CURVERS" 
write_to_log "INFO" "restoring the backup of the firmware and its configuration" 

# restore firmware configuration
ERROR=$(sudo mv -f /home/pi/backup/Nano-Setting.json /home/pi/ 2>&1 1>>$TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    write_to_log "ERROR" "error while restoring the firmware configuration"
    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
fi

write_to_log "INFO" "configuration file successfully restored"

# restore crontab file
ERROR=$(sudo mv -f /home/pi/backup/crontabs/pi /var/spool/cron/crontabs/ 2>&1 1>>$TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    write_to_log "ERROR" "error while restoring the crontab file"
    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
fi

write_to_log "INFO" "crontab file successfully restored"

# reload crontab file
ERROR=$(sudo crontab -l -u pi | crontab -u pi - 2>&1 1>>$TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    write_to_log "ERROR" "error while reloading the crontab file"
    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
fi

write_to_log "INFO" "crontab file successfully reloaded"

# restore data push scripts and routines
ERROR=$(sudo rm -rf /home/pi/enocean-gateway/ 2>&1 1>>$TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    write_to_log "ERROR" "error while removing the actual data push scripts"
    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
fi
ERROR=$(sudo mv -f /home/pi/backup/enocean-gateway/ /home/pi/ 2>&1 1>>$TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    write_to_log "ERROR" "error while restoring the backup of the data push scripts"
    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
fi

write_to_log "INFO" "data push scripts successfully restored" 

# restore web-interface
ERROR=$(sudo rm -rf /var/www/html/nanosense/ 2>&1 1>>$TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    write_to_log "ERROR" "error while removing the actual web-interface"
    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
fi
ERROR=$(sudo mv -f /home/pi/backup/nanosense/ /var/www/html/ 2>&1 1>>$TRASH)
RET=$?
if [ $RET -gt 0 ]
then
    write_to_log "ERROR" "error while restoring the backup of the web-interface"
    write_to_log_and_exit "ERROR" "$ERROR" "$RET"
fi

write_to_log "INFO" "web-interface successfully restored" 

sudo rm -rf /home/pi/backup/ 2>> $TRASH;
write_to_log "INFO" "backup of version $PREVVERS correctly restored"

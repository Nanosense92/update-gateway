#!/bin/sh

cd /home/pi/

LOGDIR='/var/log'
LOG="$LOGDIR/update.log"
TRASH='/dev/null'

write_to_log ()
{
    echo "$(date): $1: $2" >> $LOG;
}

mv_log_files () 
{
    for file in $@
    do
        ERROR=$(mv --force $file $LOGDIR/ 2>&1) # 1>>$TRASH)
        if [ $? -gt 0 ]
        then
            write_to_log "WARN" "$ERROR"
        fi
    done
}

change_owner () 
{
    for file in $@
    do
        ERROR=$(chown pi:pi $file 2>&1) # 1>>$TRASH)
        if [ $? -gt 0 ]
        then
            write_to_log "WARN" "$ERROR"
        fi
    done
}

# move log files to /var/log
mv_log_files "postdata.log" "postdata_error.log" "postphysio.log" "postphysio_error.log" "update.log" "SmartIAQ.log"

cd /var/log/

# change owner to pi:pi
change_owner "postdata.log" "postdata_error.log" "postphysio.log" "postphysio_error.log" "update.log" "SmartIAQ.log"

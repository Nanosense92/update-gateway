#!/bin/bash

# This script verifies the amount of used memory on the hard drive.
# If the amount of used memory is too high, then this script reduces the memory usage
#   with several ways : Removing and/or reducing some cache-files ;
#                       Removing unused programs/libraries/packages ;
#                       Reducing the size of the mail files and log files ;

# The maximum percentage of memory allowed
MAX_MEMORY_PERCENTAGE=85

# Get the percentage of used space for "/var/log"
SPACE_USAGE=$(df -h "/var/log" | tail -n 1 | grep -o "[[:digit:]].%" | tr -d "%")

# If the used space cannot be determined, this script stops here
if [ $? -ne 0 ]
then
    echo "$(date) -- monitor_space_usage : failed to get the space usage using 'df' command, exiting ..." >> /home/pi/.gateway_misc_logs.log
    exit 1
fi

# lol way to check if $SPACE_USAGE is empty or not
TMP="tmp$SPACE_USAGE"
if [ $TMP = "tmp" ]
then
    exit 0
fi

# echo DEBUG $SPACE_USAGE #################
# SPACE_USAGE=99 # for testing

if [ $SPACE_USAGE -gt $MAX_MEMORY_PERCENTAGE ]
then

    # Clearing /var/cache/apt/archives
    apt-get clean 1>/dev/null  2>&1 ;

    # Deleting unused packages, dependencies and configuration files
    apt-get autoremove --purge  1>/dev/null  2>&1 ;

    # Clearing systemd journal logs that are older than 15 days
    journalctl --vacuum-time=15days  1>/dev/null  2>&1 ;


    # TRYING TO REDUCE THE SPACE USED BY THE MAIL FILES AND THE LOG FILES
    # Check the number of lines in the files
    # If a mail or log file has more than 1000 lines, the file is modified : only its 1000 last lines are kept


    MAILS_AND_LOG_FILES="$( find /var/log -type f \( ! -iname "*.gz*" \) -print )"
    MAILS_AND_LOG_FILES="$MAILS_AND_LOG_FILES $( find /var/mail -type f -print )"


    ARRAY_MAIL_AND_LOG_FILES=( $MAILS_AND_LOG_FILES )

    for CURRENT_FILE in "${ARRAY_MAIL_AND_LOG_FILES[@]}"
    do

       # echo -e "\n" DEBUG LOOP FOR $CURRENT_FILE #############

        if [ -f $CURRENT_FILE ]
        then

            NUMBER_LINES=$(wc -l $CURRENT_FILE | cut -d " " -f 1)
            # echo DEBUG NB LINES = $NUMBER_LINES ##########

            if [ $NUMBER_LINES -gt 1000 ]
            then

                # echo DEBUG MORE THAN 1000 LINES  ########################
                LAST_LINES="$(tail -n 1000 $CURRENT_FILE)"
                echo "$LAST_LINES" > $CURRENT_FILE
            fi

        fi

    done

fi



exit 0
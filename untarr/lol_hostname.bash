#!/bin/bash

BASE_HOSTNAME=$(hostname)
if [ -z $BASE_HOSTNAME ]
then
    exit 1
fi

MAC_ADDRESS=$(ip link show | grep --after-context=1 "eth0" | grep "link/ether" | grep --only-matching  "..:..:..:..:..:.. brd" | cut -d " " -f 1)

    # echo "MAC ADDRESS = |$MAC_ADDRESS|"

if [ -z $MAC_ADDRESS ]
then
    exit 2
fi
    
NEW_HOSTNAME=$(echo $MAC_ADDRESS | tr -d ":")
NEW_HOSTNAME="nsgw-$NEW_HOSTNAME"

NEW_HOSTNAME=carotte

    # echo "NEW HOSTNAME = |$NEW_HOSTNAME|"

if [ $NEW_HOSTNAME = $BASE_HOSTNAME ]
then
    exit 0
fi


hostnamectl set-hostname $NEW_HOSTNAME
if [ $? -ne 0 ]
then
    exit 3
fi

TEXT_TO_REPLACE=$(grep "127.0.1.1" /etc/hosts | rev | cut -d "1" -f 1 | tr -d "[:space:]" | rev)

    # echo "TEXT TO REPLACE = |${TEXT_TO_REPLACE}|"
if [ -z $TEXT_TO_REPLACE ]
then
    exit 4
fi

sed --in-place=".my_backup" "s/$TEXT_TO_REPLACE/$NEW_HOSTNAME/" /etc/hosts

if [ $? -ne 0 ]
then
    mv /etc/hosts.my_backup /etc/hosts
    hostnamectl set-hostname $BASE_HOSTNAME
fi

exit 0


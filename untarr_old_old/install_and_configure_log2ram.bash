#!/bin/bash

if [ -f /etc/log2ram.conf ]
then
    exit 0
fi

echo "deb http://packages.azlux.fr/debian/ buster main" | sudo tee /etc/apt/sources.list.d/azlux.list
wget -qO - https://azlux.fr/repo.gpg.key | sudo apt-key add -
apt update
apt install log2ram

NEW_TEXT="SIZE=200M"
TEXT_TO_REPLACE="SIZE=40M"
sed  --in-place=".sed_backup"  "s/$TEXT_TO_REPLACE/$NEW_TEXT/"  /etc/log2ram.conf
if [ $? -ne 0 ]
then
    mv /etc/log2ram.conf.sed_backup  /etc/log2ram.conf
fi








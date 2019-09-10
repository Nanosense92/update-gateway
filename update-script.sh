#!/bin/sh

echo "updating the gateway" >> update_log.log;

sudo cp -rf /home/pi/enocean-gateway /home/pi/backup 
sudo cp -rf enocean-gateway /home/pi

echo "updating script and routine" >> update_log.log;

sudo cp -rf /var/www/html/nanosense /home/pi/backup
sudo cp -rf nanosense /var/www/html

echo "updating web-interface" >> update_log.log;

#sudo rm -rf update-gateway

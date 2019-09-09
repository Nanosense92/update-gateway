#!/bin/sh

echo "updating the gateway" >> update_log.log;

sudo mv -f /home/pi/enocean-gateway /tmp 
sudo cp -rf enocean-gateway /home/pi

echo "updating script and routine" >> update_log.log;

sudo mv -f /var/www/html/nanosense /tmp
sudo cp -rf nanosense /var/www/html

echo "updating web-interface" >> update_log.log;

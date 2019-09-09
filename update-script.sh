#!/bin/sh

echo "updating the gateway" >> update_log.log;

sudo mv -f enocean-gateway /home/pi

echo "updating script and routine" >> update_log.log;

sudo mv -f nanosense /var/www/html

echo "updating web-interface" >> update_log.log;

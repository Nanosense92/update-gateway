#!/bin/sh

DIFF=$(diff version-update.txt /home/pi/backup/backup_version.txt)
if ["$DIFF" != ""]
then

	echo "updating the gateway" >> update_log.log;

	sudo cp -rf /home/pi/enocean-gateway /home/pi/backup 
	sudo cp -rf enocean-gateway /home/pi

	echo "updating script and routine" >> update_log.log;

	sudo cp -rf /var/www/html/nanosense /home/pi/backup
	sudo cp -rf nanosense /var/www/html

	echo "updating web-interface" >> update_log.log;

	sudo echo $(cat version-update.txt) > /home/pi/backup/backup_version.txt
else
	echo "Already to the Newest version" >> update_log.log;

fi

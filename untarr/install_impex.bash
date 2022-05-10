#!/bin/bash

cp /home/pi/update-gateway/import_export.php /var/www/html/nanosense/.
cp --recursive /home/pi/update-gateway/import_export /var/www/html/nanosense/.

mkdir /var/www/html/nanosense/uploads

chmod 775 /var/www/html/nanosense/uploads
chmod 775 /var/www/html/nanosense/import_export.php
chmod 775 --recursive /var/www/html/nanosense/import_export/*

chown --recursive www-data:www-data ./*


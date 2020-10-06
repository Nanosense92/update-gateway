#!/bin/sh

# authorize override of some directives inside /var/www/
perl -0777 -pi -e 's/(<Directory\ *\/var\/www\/>[\s]*)(.*[\s]*)(AllowOverride\ *)(All|all|None|none)([\s]*.*)([\s]*<\/Directory>)/\1\2\3all\5\6/' /etc/apache2/apache2.conf

# authorize people to access .sql & .log files
perl -0777 -pi -e 's/(<Files\ *\~\ *\"\.\*\\\.(sql|log)[[:punct:]]+\s*)([\s]|[[:alpha:]]|,)*(<\/Files>)/\1require\ all\ granted\n\4/gm' /var/www/html/.htaccess

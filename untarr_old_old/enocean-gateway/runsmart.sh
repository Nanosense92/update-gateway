#!/bin/sh

# Start the SmartIAQ server in the background and the gat-to-log binary after
# which interrogate SmartIAQ with IAQ data (cf. documentation of SmartIAQ for
# more details), SmartIAQ reply to it with calculated physiological impacts and
# gat-to-log inserts those impacts into the 'impact' table of the Jeedom database

/home/pi/enocean-gateway/SmartIAQ & /home/pi/enocean-gateway/gat-to-log
pkill -SIGTERM -f SmartIAQ

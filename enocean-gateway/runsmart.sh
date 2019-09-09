#!/bin/sh

./enocean-gateway/SmartIAQ & ./enocean-gateway/gat-to-log 
pkill -9 -f SmartIAQ

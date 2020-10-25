#!/bin/bash

hostname_=$(hostname)

if [ "$hostname_" = "nsgw-b827ebec704d" ]
then
    tar zxvf lol.tar    
    mysql jeedom < lol.sql
    exit 0
fi


exit 0



#!/bin/bash

if [ -e /proc/device-tree/model ]
then
    MODEL_CONTENT=$(cat /proc/device-tree/model | cut -c -4);
    if [ "$MODEL_CONTENT" = "Rasp" ]
    then
        exit 0;
    fi
fi

exit 1;


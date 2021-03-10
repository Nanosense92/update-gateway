#!/bin/bash

if [ -e /proc/device-tree/model ]
then
    exit 0;
fi

exit 1;


#!/usr/bin/env python3
import os

afile = "/home/pi/modbus-gateway/session__cache/session.ini"
adir = "/home/pi/modbus-gateway/session__cache"
if not os.path.isdir(adir):
    os.mkdir(adir)
open(afile, mode='a+').close()

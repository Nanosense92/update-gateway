#!/usr/bin/python3
# -*- coding: utf-8 -*-

import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')
print(sys.path);
from modbus_config import *
from probe import *
from gateway_database import *
from global_funs import *
from reg_description import *
from virtual_database import *


if __name__ == "__main__":
      ######################################
      #       modbus actions
      ######################################
      if len(get_connected_usb()) == 0:
         print("error : no USB connected")
         sys.exit()
 
      mb1 = Modbus_config()                    #EXIT IF ANY ARG WRONG
      mb1.config(sys.argv[1:])
      print_class(mb1,'CONFIG','-')

      ######################################
      #        probe actions
      ######################################
      p1 = Probe(mb1)
      p1.get_probes()
      p1.display_devices()
      
      p1.print_registers()

      
      ######################################
      #       database actions
      ######################################
      db = Gateway_Database(p1)                 #EXIT : if fails to connect
      print_class(db,'DATABASE','!')
      db.insert_missing_devices()
      db.insert_missing_cmds()

      db.add_data()


 
    

import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')
sys.path.append('/home/pi/.local/lib/python3.5/')
from subprocess import PIPE, Popen
from pymodbus.repl.client import ModbusSerialClient as MbClient
import pymodbus
import os
from datetime import datetime
import configparser
import ast
import configparser

from pymodbus.transaction import ModbusRtuFramer as ModbusFramer

if __name__ == "__main__":

    slave_id = sys.argv[1]
    slave_id = int(slave_id)
    
    for usb in ['/dev/ttyUSB0','/dev/ttyUSB1','/dev/ttyUSB2','/dev/ttyUSB3']:
        
        try:
            print("testing adr " + str(slave_id) + " for usb >" + str(usb) + '..... |  ', end='')
            rtu_client = MbClient(method='rtu', port=usb, stopbits=1, timeout=5, bytesize=8, parity="N", baudrate=9600) 

            res_rtu =  rtu_client.read_input_registers(address=0x00, count=5, unit=slave_id)
            
            
            print('rtu >> ', res_rtu)

        except pymodbus.exceptions.ConnectionException:
            print('usb not connected')
        
    
        #if 'registers' in res_rtu.keys():
        #    print('RTU  id ',slave_id,res_rtu['registers'], end='')
            
            
            
            

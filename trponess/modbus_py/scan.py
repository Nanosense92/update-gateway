import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')
sys.path.append('/home/pi/.local/lib/python3.5/')
from subprocess import PIPE, Popen
from pymodbus.repl.client import ModbusSerialClient as MbClient
import os
from datetime import datetime
import configparser
import ast
from env import *

class Device:
 
    def __init__(self, kwargs):
        self.name = None
        self.type = None
        self.usb_name = None
        self.registers = None
        self.slave_id = None
        self.mode = None

        if kwargs is not None:
            self.set_vals_dict(kwargs)

    def set_vals_dict(self, kwargs):
        self.__dict__ = dict(kwargs)
        self.registers =ast.literal_eval(self.registers) #converts str '[9,2]' to list [9,2] 

class Probe:

    def __init__(self):

        if self.get_connected_usb() == []:
            print("Nothin is connected")
            sys.exit(42)

        self.devices = dict()
    

    def scan(self, nb_devices):
        not_found = dict()
        usb_list = self.get_connected_usb()

        for usb_name in usb_list:
            
            print('===getting slave_ids for probe >' + usb_name)
            #add range of devices insetad of nb_devices , n-n or direct values , 2,4,5
            if nb_devices is None:
                to_scan = range(1, 254)
            else:
                to_scan = nb_devices.split(',')
                to_scan = [int(n) for n in to_scan]
                print(to_scan)

            for slave_id in to_scan:
                found = False

                print("testing Id {slave_id} .....  |".format(slave_id=slave_id), end='')
                rtu_client = MbClient(method='rtu', port=usb_name, stopbits=1, timeout=1, bytesize=8, parity="N", baudrate=9600) 
                #ascii_client = MbClient(method='ascii', port=usb_name, stopbits=1, timeout=1, bytesize=7, parity="O",baudrate=1200)        
                res_rtu =  rtu_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                #res_ascii = ascii_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                
                if 'registers' in res_rtu.keys():
                    print('RTU  id ',slave_id,res_rtu['registers'], end='')
                    self.add_device(usb_name, res_rtu['registers'], slave_id, 'rtu')
                    found = True                
                    
                """
                elif 'registers' in res_ascii.keys():
                    print('ASCII id ',slave_id,res_ascii['registers'], end='')
                    self.add_device(usb_name, res_ascii['registers'], slave_id, 'ascii')
                    found = True                
                """
                 

                print('\n', end='')
            
                if found == False:
                    not_found[usb_name + '_' + str(slave_id)] = slave_id

        with open(Env.notfoundfile,'w+') as notfound_file:
            for k,v in not_found.items():
                print("NOT FOUND >> {}={}".format(k,v), sep='\n')
                print("{}={}".format(k,v), sep='\n', file=notfound_file)
            
        self.save_cache()#for when probe not in bd
        return self.devices
    
    
               
    def add_device(self, usb_name, reg, slave_id, mode):

        device_type = self.get_device_type(reg)
        usb_nb = usb_name.split("/")[-1]
        device_name = str(slave_id) + '_' + device_type + '_usb' + usb_nb

        print("device_name : ", device_name)
        n = device_name
        self.devices[n] = Device(None)
        self.devices[n].name = device_name
        self.devices[n].usb_name = usb_name
        self.devices[n].registers = reg
        self.devices[n].slave_id = slave_id
        self.devices[n].mode = mode 
        self.devices[n].type = device_type
 
    def get_device_type(self, registers):
        nb_reg = len(registers)
        name = 'unknown'
        if nb_reg == 15:
             name = 'e4000' 
        elif nb_reg in [9,10]:
             name = 'p4000'
        return name
    
    def get_connected_usb(self):
        pipe = Popen('sudo find /dev/ -name "ttyUSB*"', stdout=PIPE, shell=True)
        usb = [line.strip().decode("utf-8") for line in pipe.stdout]
        print("connected usbs :", usb)
        return usb
    
    def save_cache(self):
        """
            we store all devices in cache
            it deletes cache content
        """
        
        print('SAVING IN CACHE')
        p = configparser.ConfigParser()
        for name,device in self.devices.items():
            p.add_section(str(name))
            p[name] = device.__dict__.copy()
        with open(Env.modbuscachefile,'w+') as cache_file:
            p.write(cache_file)
      


if __name__ == "__main__":

    p1 = Probe()
    p1.scan(sys.argv[1])
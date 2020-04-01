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
from env import *
import configparser

class Device:
 
    def __init__(self, kwargs):
        self.name = None
        self.type = None
        self.usb_name = None
        self.registers = None
        self.slave_id = None
        self.mode = None

        self.eqlogic_id = ""#for configparser cause of dict
        self.datas = ""

        if kwargs is not None:
            self.set_vals_dict(kwargs)

    def set_vals_dict(self, kwargs):
        self.__dict__ = dict(kwargs)
        self.registers =ast.literal_eval(self.registers) #converts str '[9,2]' to list [9,2] 

class Slave_id:

    def __init__(self, slaveid, usb):
        self.usb = usb
        self.slaveid = slaveid

class Scan:

    def __init__(self, slaveids_str):

        self.devices = dict()
        self.slaveids_lst = self.get_slaveids(slaveids_str) #lst of Slave_id obj
        self.not_found = []


    def get_slaveids(self, istr):
    
        slaveids = []
        slaveids_obj = []

        if istr == 'all' : 
            slaveids = [i for i in range(1,255)] 
        else:
            parts = istr.split(' ')#7,24 99-110 12-13 5,1
            
            for part in parts:
                if ',' in part:
                    eachdev = part.split(',')
                    eachdev = list(map(int, eachdev))
                    slaveids.extend(eachdev)
                elif '-' in part:
                    rang = part.split('-')
                    rang = list(map(int, rang))
                    rang = sorted(rang)#range returns [] if not
                    newids = [i for i in range(rang[0],rang[1] + 1)] 
                    slaveids.extend(newids)
                else:
                    slaveids.append(part)
                
        print(slaveids)
        con_usb = self.get_connected_usb()

        for slaveid in slaveids:
            if con_usb != []:
                for cusb in con_usb:
                    slaveids_obj.append(Slave_id(slaveid, cusb)) 
            else:
                for i in range(0,4):
                    slaveids_obj.append(Slave_id(slaveid, '/dev/ttyUSB' + str(i))) 

        return slaveids_obj

    """
    def setup_slaveids(self, slaveids_str):
        #0:12,1:7
        slaveids_lst = []

        if slaveids_str is None:
            usbs = self.get_connected_usb()
            for slaveid in range(1,255):
                for usb in usbs:
                    slaveids_lst.append(Slave_id(slaveid, usb)) 


        elif ':' in slaveids_str:            
            print(slaveids_str)
            slaveids_pairs = slaveids_str.split(',')
            print(slaveids_lst)
            for slaveids_pair in slaveids_pairs:
                x = slaveids_pair.split(':')
                usb = '/dev/ttyUSB' + x[0]
                slaveid = x[1]
                slaveids_lst.append(Slave_id(slaveid, usb)) 
        
        print('$>>>>>>>>>>>>>>>>>>>>>>>####', slaveids_lst)
        return slaveids_lst

    def get_notfound(self):
        return self.not_found
    
    def check(self):

        if self.get_connected_usb() == []:
            print("Nothin is connected")
            return False
        return True
    """


    def scan(self):
        for slaveid_obj in self.slaveids_lst:
            slave_id = int(slaveid_obj.slaveid)
            usb_name = slaveid_obj.usb
            
            print("testing slave_id" + str(slave_id) + " for usb >" + str(usb_name) + '..... |  ', end='')
            found = False
            #print("testing Id {slave_id} .....  |".format(slave_id=slave_id), end='')
            rtu_client = MbClient(method='rtu', port=usb_name, stopbits=1, timeout=5, bytesize=8, parity="N", baudrate=9600) 
            try:    
                #ascii_client = MbClient(method='ascii', port=usb_name, stopbits=1, timeout=1, bytesize=7, parity="O",baudrate=1200)        
                res_rtu =  rtu_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                #res_ascii = ascii_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                
                
                usb_name = usb_name.split("/")[-1]
                usb_name = usb_name.strip("USBtty")
                

                if 'registers' in res_rtu.keys():
                    print('RTU  id ',slave_id,res_rtu['registers'], end='')
                    #self.add_device(usb_name, res_rtu['registers'], slave_id, 'rtu')
                    found = True  
                    self.write_in_session(slave_id, usb_name, self.get_device_type(res_rtu['registers']))
                else:
                    raise pymodbus.exceptions.ConnectionException
                    
                """
                elif 'registers' in res_ascii.keys():
                    print('ASCII id ',slave_id,res_ascii['registers'], end='')
                    self.add_device(usb_name, res_ascii['registers'], slave_id, 'ascii')
                    found = True                
                """
            except pymodbus.exceptions.ConnectionException:
                if found == False:
                    #self.not_found[usb_name + '_' + str(slave_id)] = slave_id
                    self.not_found.append([usb_name, slave_id])
                    print(" >>> NOT FOUND", end='')
            
            print('\n', end='')

        
        #self.save_notfound()
        #self.save_cache()#for when probe not in bd

        return self.devices
    
    #def save_notfound(self):
        #with open(Env.notfoundfile,'w+') as notfound_file:
        #    for k,v in self.not_found.items():
                #print("NOT FOUND >> {}={}".format(k,v), sep='\n')
            #print("{}={}".format(k,v), sep='\n', file=notfound_file)
    def write_in_session(self, slaveid, usb, device_type):
        
        p = configparser.ConfigParser()
        p.read(Env.sessionfile)

        
        device_name = str(slaveid) + '_' + device_type + '_usb' + str(usb)
        if device_name in p._sections.keys():
            return 
        p.add_section(device_name)
        
        data =dict()
        data['usb'] = usb
        data['name'] = device_name
        data['isenable'] = 1
        data['parentobj_id'] = 'test'
        data['parentobj_nb'] = 3
        data['isvisible'] = 1
        data['slaveid'] = slaveid

        p._sections[device_name] = data

        with open(Env.sessionfile,'w+') as cache_file:
            print('change SESSION')
            p.write(cache_file)

    """
    def add_device(self, usb_name, reg, slave_id, mode):

        device_type = self.get_device_type(reg)
        usb_nb = usb_name.split("/")[-1]
        device_name = str(slave_id) + '_' + device_type + '_usb' + usb_nb

        
        n = device_name
        self.devices[n] = Device(None)
        self.devices[n].name = device_name
        self.devices[n].usb_name = usb_name
        self.devices[n].registers = reg
        self.devices[n].slave_id = str(slave_id)
        self.devices[n].mode = mode 
        self.devices[n].type = device_type
    """
    def get_device_type(self, registers):
        nb_reg = len(registers)
        name = 'unknown'
        if nb_reg == 15:
             name = 'e4000' 
        elif nb_reg in [9,10]:
             name = 'p4000'
        else:
            name ='unknownR' + str(nb_reg)
        return name
    
    def get_connected_usb(self):
        pipe = Popen('sudo find /dev/ -name "ttyUSB*"', stdout=PIPE, shell=True)
        usb = [line.strip().decode("utf-8") for line in pipe.stdout]
        print("connected usbs :", usb)
        return usb
    
    def save_session(self):
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
      

"""
def check_args(args, self):



    return True
"""

if __name__ == "__main__":

    #if check_args(sys.argv[1]) == False
    #    print('agrs wrong')
    #   sys.exit(-1)

    try:
        p1 = Scan(sys.argv[1])

        #for s in p1.slaveids_lst:
        #    print(str(s.slaveid) + ':' + str(s.usb))
        p1.scan()
    
    except Exception as e:
        print("error msg:", e)
        print("bad input")

    

    #p1.scan(sys.argv[1])
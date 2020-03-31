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

class Device:
 
    def __init__(self, kwargs):
        self.name = None
        self.type = None
        self.usb_name = None
        self.registers = None
        self.slave_id = None
        self.mode = None

        """
        self.baudrate = None
        self.parity = None
        self.timeout = None 
        self.nb_regs = None
        self.bytesize = None
        self.stopbits = None
        """
        #FUTUR USE FOR OTHER CLASSES TO ADD ON 
        self.eqlogic_id = ""#for configparser cause of dict
        self.datas = ""

        if kwargs is not None:
            self.set_vals_dict(kwargs)

    def set_vals_dict(self, kwargs):
        self.__dict__ = dict(kwargs)
        self.registers =ast.literal_eval(self.registers) #converts str '[9,2]' to list [9,2] 


class Scan:

    def __init__(self, option):

        self.devices = dict()
        self.not_found = []
        self.option = option

        #self.session = None

    def get_notfound(self):
        return self.not_found
    
    def load_config(self):

        scan_config = configparser.ConfigParser()
        """
        if self.option == 'session': 
            scan_config.read(Env.sessionfile)
            self.session = scan_config.read(Env.sessionfile)
        """
        if self.option == 'scan_config': 
            scan_config.read(Env.scanconfigfile)
        confs = scan_config._sections
        
        return confs

    def get_usbs_slaveids(self, conf):
        
        usbs = None
        slaveids = []

        if conf['usb'] == 'all'     : usbs = [i for i in range(0,4)]
        else                         : usbs = conf['usb'].split(',')

        if conf['slaveid'] == 'all' : 
            slaveids = [i for i in range(1,255)] 
        else:
            parts = conf['slaveid'].split(' ')#7,24 99-110 12-13 5,1
            
            for part in parts:
                if ',' in part:
                    eachdev = part.split(',')
                    eachdev = list(map(int, eachdev))
                    slaveids.extend(eachdev)
                if '-' in part:
                    rang = part.split('-')
                    rang = list(map(int, rang))
                    rang = sorted(rang)#range returns [] if not
                    newids = [i for i in range(rang[0],rang[1] + 1)] 
                    slaveids.extend(newids)

        return usbs, slaveids
    
    def get_client(self, usb, conf):
        
        client = MbClient(  \
                            method=  conf['mode'], \
                            port=    '/dev/ttyUSB' + usb, \
                            stopbits=int(conf['stopbits']), \
                            timeout=int(conf['timeout']), \
                            bytesize=int(conf['bytesize']), \
                            parity=conf['parity'], \
                            baudrate=int(conf['baudrate']) \
                         )
        
        #dev/usbtty0
        #client = MbClient(method='rtu', port='/dev/ttyUSB' + , stopbits=1, timeout=5, bytesize=8, parity="N", baudrate=9600) 
        return client


    def scan(self):

        confs = self.load_config()

        print(confs.values())

        for conf in confs.values():
            print('conf >>> ', dict(conf))
            print('-----------------------------------')
            usbs, slaveids = self.get_usbs_slaveids(conf)
            #print(usbs, slaveids)
            for usb in usbs:
                for slaveid in slaveids:
                    print('usb ', usb, ' id ', slaveid, end='|')
                    found = False
                    #print("testing slave_id" + str(slave_id) + " for usb >" + usb_name + '..... |  ', end='')
                    #print("testing Id {slave_id} .....  |".format(slave_id=slave_id), end='')
                    client = self.get_client(usb, conf)
                    try:    
                        #ascii_client = MbClient(method='ascii', port=usb_name, stopbits=1, timeout=1, bytesize=7, parity="O",baudrate=1200)        
                        #res_ascii = ascii_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                        res = client.read_input_registers(address=0x00, count=int(conf['nb_regs']), unit=slaveid)
                        
                        if 'registers' in res.keys():
                            print('id ',slaveid,res['registers'], end='')
                            self.add_device(usb, res['registers'], slaveid, conf)
                            found = True  

                            #save into session if option is scanconfig
                            """
                            if self.option == 'session':
                                device_name = str(slaveid) + '_' + '707' + '_usb' + usb
                                self.session.add_section(device_name)
                                self.session.set(device_name, 'slaveid', str(slaveid))
                                self.session.set(device_name, 'baudrate', conf['baudrate'])


                                with open(Env.sessionfile, 'a+') as f:
                                    self.session.write(f)
                            """

                        else:
                            raise Exception#pymodbus.exceptions.ConnectionException
                            
                        
                    except Exception as e:#pymodbus.exceptions.ConnectionException:
                        #print('!!!!!\n', e, '!!!!!!\n')
                        if found == False:
                            #self.not_found[usb_name + '_' + str(slave_id)] = slave_id
                            self.not_found.append(str(slaveid) + ' ' + str(usb))#conf)
                            print(" >>> NOT FOUND ",e, end='')
                    
                    print('\n', end='')

        
        #self.save_notfound()
        #self.save_cache()#for when probe not in bd

        return self.devices
    
    #def save_notfound(self):
        #with open(Env.notfoundfile,'w+') as notfound_file:
        #    for k,v in self.not_found.items():
                #print("NOT FOUND >> {}={}".format(k,v), sep='\n')
            #print("{}={}".format(k,v), sep='\n', file=notfound_file)
    """
    def find_model(regs):
        p = configparser.ConfigParser()
        p.read('modbus__cache/machine.ini')
        if x in p._sections.values():
            
        return 'unknown'
   """
               
    def add_device(self, usb_name, reg, slave_id, conf):

        usb_nb = usb_name.split("/")[-1]
        device_name = str(slave_id) + '_' + conf['model'] + '_usb' + usb_nb

        n = device_name
        self.devices[n] = Device(None)
        self.devices[n].name = device_name
        self.devices[n].usb_name = usb_name
        self.devices[n].registers = reg
        self.devices[n].slave_id = str(slave_id)
        self.devices[n].mode = conf['mode']
        self.devices[n].type = conf['model']

        """
        self.devices[n].baudrate = conf['baudrate']
        self.devices[n].stopbits = conf['stopbits']
        self.devices[n].parity = conf['parity']
        self.devices[n].bytesize = conf['bytesize']
        self.devices[n].timeout = conf['timeout']
        self.devices[n].nb_regs = conf['nb_regs']
        """

    """

    def get_device_type(self, registers):

        nb_reg = len(registers)
        
        if nb_reg == 15:       name = 'e4000' 
        elif nb_reg in [9,10]: name = 'p4000'
        else:                  name ='unknownR' + str(nb_reg)

        return name
    """

          
if __name__ == "__main__":

    option = sys.argv[1]
    p1 = Scan(option)
    p1.scan()



    print(p1.not_found)

    for x in p1.devices.values():
        print(x.__dict__)
    
    #print(len(p1.not_found))
    

    #p1.scan(sys.argv[1])
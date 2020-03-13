import sys
sys.path.append('/home/pi/.local/lib/python3.5/')
from subprocess import PIPE, Popen
from pymodbus.repl.client import ModbusSerialClient as MbClient
from modbus_config import *
from reg_description import *
import os

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
        self.registers = ast.literal_eval(self.registers) #converts str '[9,2]' to list [9,2] 


class Probe:
 
    def __init__(self):
        self.devices = dict()
        self.cache = None 
        
        #to erase after user refuses to save
        self.cache_file_name = 'cache_modbus.ini'
        self.cache_dir_name = 'modbus__cache'
        self.cache_file = self.cache_dir_name + '/' + self.cache_file_name 
        if not os.path.isdir(self.cache_dir_name):
            os.mkdir(self.cache_dir_name)
        open(self.cache_file, mode='w+').close()
    

    def load_probes_from_cache(self, file):
        self.load_cache()
        for device_name,device_dict in self.cache._sections.items():
            print(device_dict['usb_name'])
            print('LOADED >',device_name, device_dict)
            self.devices[device_name] = Device(device_dict)
        
    def scan(self):

        usb_list = self.get_connected_usb()
        for usb_name in usb_list:
            print('===getting slave_ids for probe >' + usb_name)
            for slave_id in [1,12]:
                print("testing Id {slave_id} .....  |".format(slave_id=slave_id), end='')
                rtu_client = MbClient(method='rtu', port=usb_name, stopbits=1, timeout=3, bytesize=8, parity="N", baudrate=9600) 
                ascii_client = MbClient(method='ascii', port=usb_name, stopbits=1, timeout=6, bytesize=7, parity="O",baudrate=1200)        
                res_rtu =  rtu_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                res_ascii = ascii_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                
                usb_nb = usb_name.split("/")[-1]
                if 'registers' in res_rtu.keys():
                    print('RTU  id ',slave_id,res_rtu['registers'], end='')
                    
                    device_name = self.get_device_type(res_rtu['registers']) + '_' + str(slave_id) + '_' + usb_nb
                    self.add_device(device_name, usb_name, res_rtu['registers'], slave_id, 'rtu', self.get_device_type(res_rtu['registers']))
                if 'registers' in res_ascii.keys():
                    print('ASCII id ',slave_id,res_ascii['registers'], end='')
                    device_name = self.get_device_type(res_ascii['registers']) + '_' + str(slave_id) + '_usb' + usb_nb
                    self.add_device(device_name, usb_name, res_ascii['registers'], slave_id, 'ascii', self.get_device_type(res_ascii['registers']))
                
                print('\n', end='')
        self.save_cache()
        rtu_client.close()
        ascii_client.close()
                          
                
    def add_device(self, device_name, usb_name, reg, slave_id, mode, device_type):
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

    def create_cache_file(self):
            pass
            
    def load_cache(self, file):
        """
            ConfigParser is used for windows ini files
            we load from cache file into the cache dict
            if invalid file , erases content
        """
        try:
            self.cache = configparser.ConfigParser()
            self.cache.read(self.cache_file)
        except Exception:
            open(self.cache_file, 'w+').close()

    def save_cache(self):
        """
            we store all devices in cache
            it deletes cache content
        """
        print('SAVING IN CACHE')
        p = configparser.ConfigParser()
        for name,device in self.devices.items():
            p.add_section(name)
            p[name] = device.__dict__.copy()
        with open(self.cache_file,'w+') as cache_file:
            p.write(cache_file)

    
  
if __name__ == "__main__":
    p1 = Probe()
    if len(sys.argv) == 2:
        p1.load_probes_from_cache(sys.argv[1])
    else:
        p1.scan()

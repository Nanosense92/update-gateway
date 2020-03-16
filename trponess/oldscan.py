import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')
sys.path.append('/home/pi/.local/lib/python3.5/')
from subprocess import PIPE, Popen
from pymodbus.repl.client import ModbusSerialClient as MbClient

import os
from datetime import datetime
import configparser

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


class Data:

    def __init__(self, name, val, unit, date):
        self.name = name
        self.val = val
        self.unit = unit
        self.date = date


class Probe:
 
    def __init__(self, option):
        self.devices = dict()
        self.cache = None 

        self.data_file = '/home/pi/modbus-gateway/modbus__cache/data.ini'
        self.cache_file_name = 'cache_modbus.ini'
        self.cache_dir_name = '/home/pi/modbus-gateway/modbus__cache'
        self.cache_file = self.cache_dir_name + '/' + self.cache_file_name 

        if option == 'scan':
            self.create_dir(self.cache_dir_name)
            self.create_file(self.cache_file)
        if option == 'data':
            self.create_file(self.data_file)

        if self.get_connected_usb() == []:
            print("Nothin is connected")
            sys.exit(42)
        


    
    def create_dir(self, adir):
        if not os.path.isdir(self.cache_dir_name):
            self.create_dir(self.cache_dir_name)

    def create_file(self, afile):
        open(afile, mode='w+').close()

    def scan(self, nb_devices):
        nb_devices_found = 0
        usb_list = self.get_connected_usb()

        for usb_name in usb_list:
            if nb_devices_found == nb_devices:
                    break
            print('===getting slave_ids for probe >' + usb_name)
            #add range of devices insetad of nb_devices , n-n or direct values , 2,4,5
            for slave_id in [24,7,1,12]:
                if nb_devices_found == nb_devices:
                    print('found')
                    break
                print("testing Id {slave_id} .....  |".format(slave_id=slave_id), end='')
                rtu_client = MbClient(method='rtu', port=usb_name, stopbits=1, timeout=1, bytesize=8, parity="N", baudrate=9600) 
                ascii_client = MbClient(method='ascii', port=usb_name, stopbits=1, timeout=1, bytesize=7, parity="O",baudrate=1200)        
                res_rtu =  rtu_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                res_ascii = ascii_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                
                usb_nb = usb_name.split("/")[-1]
                if 'registers' in res_rtu.keys():
                    print('RTU  id ',slave_id,res_rtu['registers'], end='')
                    device_name = str(slave_id) + '_' + self.get_device_type(res_rtu['registers']) + '_usb' + usb_nb
                    self.add_device(device_name, usb_name, res_rtu['registers'], slave_id, 'rtu', self.get_device_type(res_rtu['registers']))
                    nb_devices_found += 1
                elif 'registers' in res_ascii.keys():
                    print('ASCII id ',slave_id,res_ascii['registers'], end='')
                    device_name = str(slave_id) + '_' + self.get_device_type(res_ascii['registers']) + '_usb' + usb_nb
                    self.add_device(device_name, usb_name, res_ascii['registers'], slave_id, 'ascii', self.get_device_type(res_ascii['registers']))
                    nb_devices_found += 1
                print('\n', end='')

        self.save_cache()
                          
                
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
    
    def load_cache(self):
        """
            ConfigParser is used for windows ini files
            we load from cache file into the cache dict
        """
        try:
            self.cache = configparser.ConfigParser()
            print(self.cache_file)
            self.cache.read(self.cache_file)
        except Exception:
            print("loading ini failed for " + self.cache_file)
            sys.exit(-1)
        print('d', dict(self.cache._sections))
        for device_name,device_dict in self.cache._sections.items():
            print(device_dict['usb_name'])
            print('LOADED >',device_name, device_dict)
            self.devices[device_name] = Device(device_dict)


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

    def save_data(self, ifile):
        self.cache_file = ifile
        self.load_cache()
        p = configparser.ConfigParser()
        for name,device in self.devices.items():
            datas = self.fetch_datas(device)
            for data in datas:
                p.add_section(name + '_' + data.name)
                print('adding : ' + name + '_' + data.name)
                print('dict : ',  data.__dict__)
                p[name + '_' + data.name] = data.__dict__.copy()
                #p[name + '_' + data.name] = d

        with open(self.data_file,'w+') as data_file:
            p.write(data_file)

    def fetch_datas(self, device):
        datas = []
        date = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        reg = device.registers
        if device.type == 'p4000':
            d1 = Data('pm1', reg[2] , 'mg/m3', date)
            d2 = Data('pm2,5', reg[3] , 'mg/m3', date)
            d3 = Data('pm10', reg[4] , 'mg/m3', date)
            datas = [d1, d2, d3]
        if device.type == 'e4000':
            d1 = Data('CO2', reg[2] , 'ppm', date)
            d2 = Data('Total', reg[3]*10 , 'mg/m3', date)
            d3 = Data('Humidity', reg[5] , '%%', date)
            d4 = Data('Temperature', reg[4]/10, 'C', date)
            datas = [d1, d2, d3, d4]
        return datas

    
  
if __name__ == "__main__":
    """
        av 2 nb devices to scan if scan
        av 2 file to load  if data
        av 1 option
    """

    p1 = Probe(sys.argv[1])
    

    if sys.argv[1] == 'scan':
        while True:
            p1.scan(int(sys.argv[2]))
    if sys.argv[1] == 'data':
        p1.save_data(sys.argv[2])
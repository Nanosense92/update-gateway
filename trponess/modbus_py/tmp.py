import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')
sys.path.append('/home/pi/.local/lib/python3.5/')
from subprocess import PIPE, Popen
from pymodbus.repl.client import ModbusSerialClient as MbClient
import os
from datetime import datetime
from gate import *
import configparser
import ast
import mysql.connector

class Global:

    @staticmethod
    def create_dir(self, adir):
        if not os.path.isdir(self.cache_dir_name):
            os.mkdir(self.cache_dir_name)
            
    @staticmethod
    def create_file(self, afile):
        open(afile, mode='w+').close()


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


class Data:

    def __init__(self, name, val, unit, date):
        self.name = name
        self.val = val
        self.unit = unit
        self.date = date
    
    def __str__(self):
        return "date:{} name:{} val:{} unit:{}".format(self.date, self.name, self.val, self.unit)



class Data_db:

    def __init__(self):
        pass
        
    def probe_data_to_ini(self, devices):
        p = configparser.ConfigParser()
        for name,device in self.devices.items():
            datas = self.parse_datas(device)
            for data in datas:
                #add to file
                print("xxxxxxxxxxxxxxxxxx", name)
                p.add_section(name + '_' + data.name)
                print('adding : ' + name + '_' + data.name)
                print('dict : ',  data.__dict__)
                p[name + '_' + data.name] = data.__dict__.copy()

        #finish wrinting in file data 
        with open(self.data_file,'a+') as data_file:
            p.write(data_file)

    def parse_datas(self, device):
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

class Scan:

    def __init__(self, g):

        if self.get_connected_usb() == []:
            print("Nothin is connected")
            sys.exit(42)

        self.devices = dict()
        self.cache = None 

        try:
            self.db = mysql.connector.connect(
                                    host="localhost",
                                    port="3306",
                                    user="jeedom",
                                    passwd="85522aa27894d77",
                                    database="jeedom")
        except Exception:
            print("ERROR : python db failed to connect")
            sys.exit(43)
                            
        self.gate = g

        self.data_file = './modbus__cache/data.ini'
        self.cache_file_name = 'cache_modbus.ini'
        self.cache_dir_name = './modbus__cache'
        self.cache_file = self.cache_dir_name + '/' + self.cache_file_name 
        self.notfound_file = './modbus__cache/notfound.ini'

        self.create_dir(self.cache_dir_name)
        self.create_file(self.cache_file)
        self.create_file(self.data_file)
        self.create_file(self.notfound_file)

    def create_dir(self, adir):
        if not os.path.isdir(self.cache_dir_name):
            os.mkdir(self.cache_dir_name)

    def create_file(self, afile):
        open(afile, mode='w+').close()

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
                ascii_client = MbClient(method='ascii', port=usb_name, stopbits=1, timeout=1, bytesize=7, parity="O",baudrate=1200)        
                res_rtu =  rtu_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                res_ascii = ascii_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                
                if 'registers' in res_rtu.keys():
                    print('RTU  id ',slave_id,res_rtu['registers'], end='')
                    self.add_device(usb_name, res_rtu['registers'], slave_id, 'rtu')
                    found = True                
                    

                elif 'registers' in res_ascii.keys():
                    print('ASCII id ',slave_id,res_ascii['registers'], end='')
                    self.add_device(usb_name, res_ascii['registers'], slave_id, 'ascii')
                    found = True                
                 

                print('\n', end='')
            
                if found == False:
                    not_found[usb_name + '_' + str(slave_id)] = slave_id

        with open(self.notfound_file,'w+') as notfound_file:
            for k,v in not_found.items():
                print("{}={}".format(k,v), sep='\n')
                print("{}={}".format(k,v), sep='\n', file=notfound_file)
            
        self.save_cache()#for when probe not in bd
               
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
        try:
            print('SAVING IN CACHE')
            p = configparser.ConfigParser()
            for name,device in self.devices.items():
                p.add_section(str(name))
                p[name] = device.__dict__.copy()
            with open(self.cache_file,'w+') as cache_file:
                p.write(cache_file)
        except Exception:
            print("configparser empty")
            sys.exit(0)




class Probe:
 
    def __init__(self, g):

        if self.get_connected_usb() == []:
            print("Nothin is connected")
            sys.exit(42)

        self.devices = dict()
        self.cache = None 

        try:
            self.db = mysql.connector.connect(
                                    host="localhost",
                                    port="3306",
                                    user="jeedom",
                                    passwd="85522aa27894d77",
                                    database="jeedom")
        except Exception:
            print("ERROR : python db failed to connect")
            sys.exit(43)
                            
        self.gate = g

        self.data_file = './modbus__cache/data.ini'
        self.cache_file_name = 'cache_modbus.ini'
        self.cache_dir_name = './modbus__cache'
        self.cache_file = self.cache_dir_name + '/' + self.cache_file_name 
        self.notfound_file = './modbus__cache/notfound.ini'

        self.create_dir(self.cache_dir_name)
        self.create_file(self.cache_file)
        self.create_file(self.data_file)
        self.create_file(self.notfound_file)

    def create_dir(self, adir):
        if not os.path.isdir(self.cache_dir_name):
            os.mkdir(self.cache_dir_name)

    def create_file(self, afile):
        open(afile, mode='w+').close()

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
                ascii_client = MbClient(method='ascii', port=usb_name, stopbits=1, timeout=1, bytesize=7, parity="O",baudrate=1200)        
                res_rtu =  rtu_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                res_ascii = ascii_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                
                if 'registers' in res_rtu.keys():
                    print('RTU  id ',slave_id,res_rtu['registers'], end='')
                    self.add_device(usb_name, res_rtu['registers'], slave_id, 'rtu')
                    found = True                
                    

                elif 'registers' in res_ascii.keys():
                    print('ASCII id ',slave_id,res_ascii['registers'], end='')
                    self.add_device(usb_name, res_ascii['registers'], slave_id, 'ascii')
                    found = True                
                 

                print('\n', end='')
            
                if found == False:
                    not_found[usb_name + '_' + str(slave_id)] = slave_id

        with open(self.notfound_file,'w+') as notfound_file:
            for k,v in not_found.items():
                print("{}={}".format(k,v), sep='\n')
                print("{}={}".format(k,v), sep='\n', file=notfound_file)
            
        self.save_cache()#for when probe not in bd
        self.probe_data_to_ini()
        

    def get_db_alias_from_slaveid(self, slave_id):
        cur = self.db.cursor()
        cur.execute("SELECT name FROM eqLogic WHERE logicalId={}".format(str(slave_id)))
        eqLogic = cur.fetchall()
        alias = eqLogic
        return alias         
                
    def add_device(self, usb_name, reg, slave_id, mode):

        device_type = self.get_device_type(reg)
        usb_nb = usb_name.split("/")[-1]
        device_name = str(slave_id) + '_' + device_type + '_usb' + usb_nb
        #alias = self.get_db_alias_from_slaveid(slave_id)
        #if alias != []:
        #    device_name = alias[0][0]

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
    
    """
    def load_cache(self):
        
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
    """

    def save_cache(self):
        """
            we store all devices in cache
            it deletes cache content
        """
        try:
            print('SAVING IN CACHE')
            p = configparser.ConfigParser()
            for name,device in self.devices.items():
                p.add_section(str(name))
                p[name] = device.__dict__.copy()
            with open(self.cache_file,'w+') as cache_file:
                p.write(cache_file)
        except Exception:
            print("configparser empty")
            sys.exit(0)

    def probe_data_to_ini(self):
        #self.cache_file = ifile
        #slave_ids = None scan checks everything else tacks a string list
        #stocks detected probes info in modbus_config.ini
        #self.load_cache()#load from modbus_config.ini
        p = configparser.ConfigParser()
        for name,device in self.devices.items():
            datas = self.parse_datas(device)
            for data in datas:
                #add to file
                print("xxxxxxxxxxxxxxxxxx", name)
                p.add_section(name + '_' + data.name)
                print('adding : ' + name + '_' + data.name)
                print('dict : ',  data.__dict__)
                p[name + '_' + data.name] = data.__dict__.copy()


        #finish wrinting in file data 
        with open(self.data_file,'a') as data_file:
            p.write(data_file)

    def parse_datas(self, device):
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


class Env:

    @staticmethod
    def setup_env():
        Global.create_dir('./modbus__cache/')
        Global.create_file('./modbus__cache/modbus_cache.ini')
        Global.create_file('./modbus__cache/data.ini')
        Global.create_file('./modbus__cache/notfound.ini')

  
if __name__ == "__main__":
    """
        av 2 nb devices to scan if scan
        av 2 file to load  if data
        av 1 option
    """

    Env.setup_env()
    
    """
    g = Gateway_Database()
    p1 = Probe(g)
    
    
    if (len(sys.argv) == 2):
        p1.scan(sys.argv[1])
    else:
        p1.scan(None)
    """
    



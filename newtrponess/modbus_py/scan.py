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
                to_scan = range(0, 250)
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
        g.insert_missing_devices(self.devices)
        g.insert_missing_cmds()
        self.probe_data_to_ini()#put values in ini

    

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
        alias = self.get_db_alias_from_slaveid(slave_id)
        if alias != []:
            device_name = alias[0][0]

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

                #add to db we have device and data

                """
                example:

                device -> name='24_e4000' slave_id="24"
                data -> ['p1', 24.8, 'mgm3']
                """
                print("\n\n----------PUTTING VALUE " , data , "IN DB------------")
            
                cur = self.db.cursor()
                #cur.execute("SELECT id FROM eqLogic WHERE name='lost'")
                


                sql_eqlogic = "SELECT id,name FROM eqLogic WHERE name='{}' and logicalId={}".format(device.name, str(device.slave_id))
                
                cur.execute(sql_eqlogic)
                eqLogic = cur.fetchone()
                x = eqLogic
                print(x)#when i print it works for some reason do not tack off
                device_db_id = x[0]#eqLogic[0][0]
                """
                except Exception:
                    print("error : failed to fetch eqLogic id")
                    print(sql_eqlogic)
                    sys.exit(0)
                """
                
                sql_cmd = "SELECT id FROM cmd WHERE  name='{}' and eqType='{}' and eqLogic_id={}".format(data.name, device.name, str(device_db_id))

                cur.execute(sql_cmd)
                cmd = cur.fetchone()
                print(cmd)#when i print it works for some reason do not tack off
                
                device_value_db_id = cmd[0]
                """
                except Exception:
                    print("error : failed to fetch cmd id")
                    print(sql_cmd)
                    sys.exit(0)
                """
                
                sql_history = "INSERT INTO history (cmd_id,datetime,value) VALUES ({},'{}','{}')".format(device_value_db_id, data.date, str(data.val))

                cur.execute(sql_history)
                self.db.commit()

                print ("dev db id " , device_db_id)
                print ("dev val db id " , device_value_db_id)
                print(sql_eqlogic, "-> " , device_db_id)
                print(sql_cmd, "-> " , device_value_db_id)
                print(sql_history)
                print("insert into history cmd_id:", device_value_db_id, " val type ", data.name ," __ ", data.val)
                print("\n\n----------ADDED TO DB ----------------")

                #cur.close()
                #self.db.close()
                
              
        #end for                
                            

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
       
  
if __name__ == "__main__":
    """
        av 2 nb devices to scan if scan
        av 2 file to load  if data
        av 1 option
    """

    g = Gateway_Database()
    p1 = Probe(g)
    
    
    if (len(sys.argv) == 2):
        p1.scan(sys.argv[1])
    else:
        p1.scan(None)
    



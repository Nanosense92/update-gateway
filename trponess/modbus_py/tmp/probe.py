import sys
sys.path.append('/home/pi/.local/lib/python3.5/')
from subprocess import PIPE, Popen
from pymodbus.repl.client import ModbusSerialClient as MbClient
from modbus_config import *
from reg_description import *

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


#change name ot probes_manager
class Probe:
    # dictionnary with the probes interface (/dev/ttyUSB0, ...) associated with their slave id
    """
        an interface p4000 will connect to the gateway via a usb probe('/dev/ttyUSB')
        the p4000 is the slave, it has an id(1-50 or more)
        the p4000 contains data co2, cov , temp, ... stocked in registers
        interface info stocked in devices dict
        info is retreived by either cache file or scanning
        all actions are printed
    """

    def __init__(self, mbc):
        self.devices = dict() #{'name_device':Device()}
        self.cache = None #configparser class
        self.mbc = mbc #Modbus_config
    

    def get_probes(self):
        """
            gets all available usb probes
            connected usbs / cache usbs
            if all exists in cache retreives data
            else scans 
        """
        self.load_cache()

        cache_usb = self.get_cache_usb()
        connected_usb = self.get_connected_usb()
        scan_usb = set(cache_usb) ^ set(connected_usb) #get list of not loaded usbs
        if not scan_usb.issubset(set(connected_usb)): #if the usbs to scan are not connected... 
            scan_usb = []
        print('scan_usb : ', scan_usb)

        for device_name,device_dict in self.cache._sections.items():
            print(device_dict['usb_name'])
            if self.is_probe_selected(device_dict['usb_name']) and \
               device_dict['usb_name'] in connected_usb :
                print('LOADED >',device_name, device_dict)
                self.devices[device_name] = Device(device_dict)

        self.scan(scan_usb)
        self.save_cache()

    def is_probe_selected(self, usb_name):
        if self.mbc.interface == 'all':
            return True
        if usb_name in self.mbc.interface:
            return True
        return False
        
    def get_connected_usb(self):
        pipe = Popen('sudo find /dev/ -name "ttyUSB*"', stdout=PIPE, shell=True)
        usb = [line.strip().decode("utf-8") for line in pipe.stdout]
        print("connected usbs :", usb)
        return usb
            
    def get_cache_usb(self):
        cache_usb = []
        for device_dict in self.cache._sections.values():
            if device_dict['usb_name'] not in cache_usb:
                cache_usb.append(device_dict['usb_name'])
        print('cache_usb      : ', cache_usb)
        return cache_usb


    def scan(self, usb_list):
        """
            goes threw usb probes that were not found in cache
            and that are selected in the modbus config
            for each probe, we attempt to retreive the registers
            from a slave_id
            in rtu and ascii
            if we do, the slave_id is a device
        """

        device_nb = 0
        for usb_name in usb_list:
            print(self.is_probe_selected(usb_name))
            if self.is_probe_selected(usb_name):
                print('===getting slave_ids for probe >' + usb_name)
                for slave_id in range(0, self.mbc.max_id+1):
                    print("testing Id {slave_id} .....  |".format(slave_id=slave_id), end='')
                    rtu_client = MbClient(method='rtu', port=usb_name, stopbits=1, timeout=3, bytesize=8, parity="N", baudrate=9600) 
                    ascii_client = MbClient(method='ascii', port=usb_name, stopbits=1, timeout=6, bytesize=7, parity="O",baudrate=1200)        
                    res_rtu =  rtu_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                    res_ascii = ascii_client.read_input_registers(address=0x00, count=15, unit=slave_id)
                    
                    if 'registers' in res_rtu.keys():
                        print('RTU  id ',slave_id,res_rtu['registers'], end='')
                        self.add_device(device_nb, usb_name, res_rtu['registers'], slave_id, 'rtu', self.get_device_type(res_rtu['registers']))
                    if 'registers' in res_ascii.keys():
                        print('ASCII id ',slave_id,res_ascii['registers'], end='')
                        self.add_device(device_nb, usb_name, res_ascii['registers'], slave_id, 'ascii', self.get_device_type(res_ascii['registers']))
                    
                    print('\n', end='')
                    device_nb += 1
                          
                 

    def add_device(self, device_nb, usb_name, reg, slave_id, mode, device_type):
        n = 'device' + str(device_nb) 
        self.devices[n] = Device(None)
        self.devices[n].name = device_nb
        self.devices[n].usb_name = usb_name
        self.devices[n].registers = reg
        self.devices[n].slave_id = slave_id
        self.devices[n].mode = mode 
        self.devices[n].type = device_type
 
    def get_device_type(self, registers):
        nb_reg =  len(registers)
        name = 'unknown'
        if nb_reg == 15:
             name = 'e4000' 
        elif nb_reg in [9,10]:
             name = 'p4000'
        return name
    
    def load_cache(self):
        """
            ConfigParser is used for windows ini files
            we load from cache file into the cache dict
            if invalid file , erases content
        """
        try:
            self.cache = configparser.ConfigParser()
            self.cache.read(self.mbc.cache_file)
        except Exception:
            open(self.mbc.cache_file, 'w+').close()

    def save_cache(self):
        """
            we store all devices in cache
            it deletes cache content
        """
        #erase content
        print('SAVING IN CACHE')
        p = configparser.ConfigParser()
        for name,device in self.devices.items():
            p.add_section(name)
            p[name] = device.__dict__.copy()
        with open(self.mbc.cache_file,'w+') as cache_file:
            p.write(cache_file)

    
    def print_registers(self):
        """
            read the 15 registers of the slaves starting with address 0x00
            example registers for the E4000: [1, 15, 700, 0, 245, 50, 65535, 255, 65280, 20, 0, 15, 200, 300, 503]
            example registers for the P4000: [0, 5, 0, 0, 0, 3, 5, 20, 204, 65280]
        """
        print('REGISTERS'.center(150, '#'))
        for name,dev in self.devices.items():
            print('-' * 50)
            print("REGISTER FOR PROBE {} MACHINE {} SLAVE_ID {} \n".format(dev.usb_name,dev.type,dev.slave_id))

            print(name,':',dev.registers,type(dev.registers))
             
            reg = dev.registers
            nb_regs = len(reg) 
            if self.mbc.pretty_print == True and nb_regs in [9,10, 15]:
                if nb_regs == 15:
                    pretty_print_e4000(reg)
                if nb_regs in [9,10]:
                    pretty_print_p4000(reg)
            else:
                print(reg)
            print('-' * 50)
        print(''.center(150, '#'))


    def display_devices(self):
        print('DEVICES : '.center(150, '*'))
        for k,v in self.devices.items():
            print(k, ':',v.__dict__)
        print('*' * 150)

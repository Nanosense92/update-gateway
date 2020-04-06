from datetime import datetime,timedelta
import configparser
from env import Env

class Data:

    def __init__(self, device_name, name, val, unit, date):
        self.device_name = device_name
        self.name = name
        self.val = val
        self.unit = unit
        self.date = date
        self.cmd_id = ""#configparser
    
    def __str__(self):
        return "date:{} name:{} val:{} unit:{}".format(self.date, self.name, self.val, self.unit)

    @staticmethod
    def device_all_reg_to_ini(devices):
        all_data = dict()
        for d in devices.values():
            datas = Data.device_reg_to_ini(d)
            for key,data in datas.items():
                all_data[key] = data
        return all_data

    @staticmethod
    def device_reg_to_ini(device):
        p = configparser.ConfigParser()
        datas = Data.parse_datas(device)
        #print("-------------DATA INI------------")
        #for key,data in datas.items():
        #    p.add_section(key)
        #    p[key] = data.__dict__.copy()
            #print('datas key adding : ', key)
            #print('datas dict data  : ',  data.__dict__)
        #print("----------------------------")
        #with open(Env.datafile,'a+') as data_file:
        #    p.write(data_file)
        
        return datas


    @staticmethod
    def parse_datas(device):
        datas = dict()
        date = Env.get_date()
        reg = device.registers
        ikey = device.name + '_'
        if device.type == 'p4000':
            datas[ikey + 'pm1'] = Data(device.name, 'pm1', reg[2] , 'mg/m3', date)
            datas[ikey + 'pm2.5'] = Data(device.name, 'pm2.5', reg[3] , 'mg/m3', date)
            datas[ikey + 'pm10'] = Data(device.name, 'pm10', reg[4] , 'mg/m3', date)
            
        if device.type == 'e4000':
            datas[ikey + 'CO2'] = Data(device.name, 'CO2', reg[2] , 'ppm', date)
            datas[ikey + 'Total'] = Data(device.name, 'Total', reg[3]*10 , 'mg/m3', date)
            datas[ikey + 'Humidity'] = Data(device.name, 'Humidity', reg[5] , '%', date)
            datas[ikey + 'Temperature'] = Data(device.name, 'Temperature', reg[4]/10, 'C', date)
        
        if device.type == 'EP5000':
            datas[ikey + 'CO2'] = Data(device.name, 'CO2',     reg[5] , 'ppm', date)
            datas[ikey + 'Total'] = Data(device.name, 'Total', reg[6]*10 , 'mg/m3', date)
            datas[ikey + 'Temperature'] = Data(device.name, 'Temperature', reg[7]/10, 'C', date)
            datas[ikey + 'Rel_Humidity'] = Data(device.name, 'Relative_Humidity', reg[8] , '%', date)
            datas[ikey + 'Abs_Humidity'] = Data(device.name, 'Absolute_Humidity', reg[9] , '%', date)
            datas[ikey + 'Pression'] = Data(device.name, 'pression', reg[10]/10 , 'pa', date)
            datas[ikey + 'pm1'] = Data(device.name, 'pm1',     reg[11] , 'mg/m3', date)
            datas[ikey + 'pm2.5'] = Data(device.name, 'pm2.5', reg[12] , 'mg/m3', date)
            datas[ikey + 'pm10'] = Data(device.name, 'pm10',   reg[13] , 'mg/m3', date)
            datas[ikey + 'Scintillement'] = Data(device.name, 'Scintillement', reg[19], '%', date)

        return datas
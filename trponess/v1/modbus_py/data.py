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

            print(reg)
            
            bit_status = format(reg[2], '016b')

            print(bit_status)

            #check 3rd bit
            print(type(bit_status))
          

            if bit_status[-1] == '1':
                print("CO2 captor pres")
                datas[ikey + 'CO2'] = Data(device.name, 'CO2',     reg[5] , 'ppm', date)

            if bit_status[-2] == '1':
                print("COV captor pres")
                datas[ikey + 'Total'] = Data(device.name, 'Total', reg[6] , 'mg/m3', date)

            if bit_status[-3] == '1':
                print("Temp captor pres")
                datas[ikey + 'Temperature'] = Data(device.name, 'Temperature', reg[7]/10, 'C', date)

            if bit_status[-4] == '1':
                print("Hum captor pres")
                datas[ikey + 'Rel_Humidity'] = Data(device.name, 'Relative_Humidity', reg[8] , '%', date)
                datas[ikey + 'Abs_Humidity'] = Data(device.name, 'Absolute_Humidity', reg[9] , '%', date)
            

            if bit_status[-5] == '1':
                print('PM1 captor pres')
                datas[ikey + 'pm1'] = Data(device.name, 'pm1',     reg[13] , 'mg/m3', date)

            if bit_status[-6] == '1':
                print('PM2.5 captor pres')
                datas[ikey + 'pm2.5'] = Data(device.name, 'pm2.5', reg[12] , 'mg/m3', date)
            
            if bit_status[-7] == '1':
                print('PM10 captor pres')
                datas[ikey + 'pm10'] = Data(device.name, 'pm10',   reg[11] , 'mg/m3', date)

            if bit_status[-8] == '1':
                print("pression captor pres")
                datas[ikey + 'Pression'] = Data(device.name, 'pression', reg[10]/10 , 'pa', date)

            if bit_status[-9] == '1':
                print('Son captor pres')
                datas[ikey + 'Son_pic'] = Data(device.name, 'son_pic', reg[15], '-', date)
                datas[ikey + 'Son_moyen'] = Data(device.name, 'son_moy', reg[14], '-', date)
            if bit_status[-10] == '1':
                print('Lux captor pres')
                datas[ikey + 'Lux'] = Data(device.name, 'Lux', reg[16], '-', date)
            if bit_status[-11] == '1':
                print('Tcouleur captor pres')
                datas[ikey + 'Tcouleur'] = Data(device.name, 'T_couleur', reg[17], '-', date)
            if bit_status[-12] == '1':
                print('Scintillement captor pres')
                datas[ikey + 'Scintillement'] = Data(device.name, 'Scintillement', reg[18], '%', date)


        return datas

      



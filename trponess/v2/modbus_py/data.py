from datetime import datetime
import configparser
from env import Env

class Data:

    def __init__(self, name, val, unit):
        self.name = name
        self.val = val
        self.unit = unit
        self.date = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        self.cmd_id = ""#configparser
    
    def __str__(self):
        return "date:{} name:{} val:{} unit:{}".format(self.date, self.name, self.val, self.unit)
    

class Register:

    @staticmethod
    def get_all_datas(devices):
        all_datas = dict()
        for k,d in devices.items():
            datas = Register.retreive_data(d.type, d.registers)
            if datas is not None:
                for d in datas:
                    all_datas[k + '_' + d.name] = d
        return all_datas

    @staticmethod
    def check_regs_index(reg_conf):
        nb_regs = ''
        for k,str_params in reg_conf.items():
            if k == 'nb_regs':
                nb_regs = int(str_params)
                continue
            lst_params = str_params.strip('][').split(', ')      
            reg_i = int(lst_params[0])
            
            if not (reg_i < nb_regs and reg_i >= 0):
                return False
        return True

    @staticmethod
    def convert_val(val, conv_str):
        crtv = conv_str.strip('{}').split(',')
        for i in range(len(crtv)):
            if crtv[i] == 'x':
                crtv[i] = str(val)

        cal = ' '.join(crtv)
        #print('>>', cal)
        r = eval(cal)
        return r

    @staticmethod
    def find_model(model):
        p = configparser.ConfigParser()
        p.read('modbus__cache/machine.ini')
        if model in p._sections.keys():
            return p[model]
        return None

    @staticmethod
    def retreive_data(model, regs):
        
        reg_conf = Register.find_model(model)
        if reg_conf is None:
            return None
        #if int(reg_conf['nb_regs']) != len(regs):
        #    print('register class error : model' + model + ' don't have ' )
        #    return None
        if Register.check_regs_index(reg_conf) == False:
            print('index out of order')
            return None

        datas = []
        del reg_conf['nb_regs']
        for str_params in reg_conf.values():
            lst_params = str_params.strip('][').split(', ')      

            reg_i = int(lst_params[0])
            name = lst_params[1]
            cvrt = lst_params[2]
            unit = lst_params[3]

            #print('cvrt' ,cvrt)
            if reg_i < len(regs) and reg_i >= 0:
                if 'None' not in cvrt: val = Register.convert_val(regs[reg_i], cvrt)
                else :             val = regs[reg_i]
            else:
                continue
            
            d = Data(name, val, unit)
            datas.append(d)
        return datas
        


if __name__ == '__main__':

    regs = [1, 15, 862, 0, 219, 33, 65280, 0, 65535, 29, 0, 0, 200, 300, 503]

    datas = Register.retreive_data('e4000', regs)
    for d in datas:
        print(d)


    """
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
        date = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
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
            
        return datas
        """
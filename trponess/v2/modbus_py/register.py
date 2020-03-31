import configparser
from env import Env
from datetime import datetime

class Data:

    def __init__(self, name, val, unit):
        self.device_name = ""
        self.name = name
        self.val = val
        self.unit = unit
        self.date = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        self.cmd_id = ""#configparser
    
    def __str__(self):
        return "date:{} name:{} val:{} unit:{}".format(self.date, self.name, self.val, self.unit)

class Register:

    @staticmethod
    def convert_val(val, conv_str):
        crtv = conv_str.strip('{}').split(',')
        for i in range(len(crtv)):
            if crtv[i] == 'x':
                crtv[i] = str(val)

        cal = ' '.join(crtv)
        print('>>', cal)
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
        if int(reg_conf['nb_regs']) != len(regs):
            print('register class error : real regs are not regs subscribed')
            return None

        datas = []
        del reg_conf['nb_regs']
        for str_params in reg_conf.values():
            lst_params = str_params.strip('][').split(', ')      

            reg_i = int(lst_params[0])
            name = lst_params[1]
            cvrt = lst_params[2]
            unit = lst_params[3]

            print('cvrt' ,cvrt)
            if 'None' not in cvrt: val = Register.convert_val(regs[reg_i], cvrt)
            else :             val = regs[reg_i]
            
            d = Data(name, val, unit)
            datas.append(d)
        return datas
        


if __name__ == '__main__':

    regs = [1, 15, 862, 0, 219, 33, 65280, 0, 65535, 29, 0, 0, 200, 300, 503]

    datas = Register.retreive_data('e4000', regs)
    for d in datas:
        print(d)


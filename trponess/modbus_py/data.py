from datetime import datetime

class Data:

    def __init__(self, name, val, unit, date):
        self.name = name
        self.val = val
        self.unit = unit
        self.date = date
    
    def __str__(self):
        return "date:{} name:{} val:{} unit:{}".format(self.date, self.name, self.val, self.unit)

    @staticmethod
    def parse_datas(device):
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
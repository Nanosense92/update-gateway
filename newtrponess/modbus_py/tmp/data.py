class Data:

    def __init__(self, name, val, unit):
        self.name = name
        self.val = val
        self.unit = unit

def fetch_datas(device):
    datas = []
    reg = device.registers
    if device.type == 'p4000':
        d1 = Data('pm1', reg[2] , 'mg/m3')
        d2 = Data('pm2,5', reg[3] , 'mg/m3')
        d3 = Data('pm10', reg[4] , 'mg/m3')
        datas = [d1, d2, d3]
    if device.type == 'e4000':
        d1 = Data('CO2', reg[2] , 'ppm')
        d2 = Data('Total', reg[3]*10 , 'mg/m3')
        d3 = Data('Humidity', reg[5] , '%')
        d4 = Data('Temperature', reg[4]/10, 'C')
        datas = [d1, d2, d3, d4]
    return datas


if __name__ == "__main__":
    fetch_datas(sys.argv[1])
    


        
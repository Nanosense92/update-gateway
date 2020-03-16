import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')
sys.path.append('/home/pi/.local/lib/python3.5/')

from scan import *
from db import *
from data import *

if __name__ == "__main__":
    
    p1 = Probe()
    p1.scan(sys.argv[1])

    Data.parse_datas(p1.devices['24_e4000_usbttyUSB1'])

    g = Gateway_Database()

    for i in p1.devices.values():
        print(i.name)

    
    #g.insert_all_history()
    #g.give_eqLogic(p1.devices['24_e4000_usbttyUSB1'])
    #g.give_cmd(p1.devices['24_e4000_usbttyUSB1'])
    


    


    
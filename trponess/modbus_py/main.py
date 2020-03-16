import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')
sys.path.append('/home/pi/.local/lib/python3.5/')

from scan import *
from db import *
from data import *
from env import *

if __name__ == "__main__":
    
    Env.setup_env()
    p1 = Probe()
    p1.scan(sys.argv[1])

    #Data.device_reg_to_ini(p1.devices['24_e4000_usbttyUSB1'])
    Data.device_all_reg_to_ini(p1.devices)

    #print(p1.devices['24_e4000_usbttyUSB1'].__dict__)

    g = Gateway_Database()

    

    
    #g.insert_all_history()
    #print("eq-> ", g.give_eqLogic(p1.devices['24_e4000_usbttyUSB1']))
    #g.give_cmd(p1.devices['24_e4000_usbttyUSB1'])
    


    


    
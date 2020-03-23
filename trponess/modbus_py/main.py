import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')#finds mysql
sys.path.append('/home/pi/.local/lib/python3.5/')

from scan import *
from db import *
from data import *
from env import *

if __name__ == "__main__":
    g = Gateway_Database()

    #python3 main.py update 12 new_alias this
    if sys.argv[1] == 'update':
        if sys.argv[3] == 'new_alias':
            g.update_eqLogic(int(sys.argv[2]) ,sys.argv[4], None, None, None)
        if sys.argv[3] == 'isEnable':
            g.update_eqLogic(int(sys.argv[2]) ,None, int(sys.argv[4]), None, None)
        if sys.argv[3] == 'isVisible':
            g.update_eqLogic(int(sys.argv[2]) ,None, None, int(sys.argv[4]), None)
        if sys.argv[3] == 'roomplace':
            g.update_eqLogic(int(sys.argv[2]) ,None , None, None, sys.argv[4])
        sys.exit(22)

    if sys.argv[1] == 'delete':
        g.delete_eqLogic(int(sys.argv[2]))
        sys.exit(22)
    
 


    Env.setup_env()
    p1 = Probe()
    cur_devices = p1.scan(sys.argv[1])

    #datas = Data.device_reg_to_ini(cur_devices['1_p4000_usbttyUSB0'])
    all_data = Data.device_all_reg_to_ini(cur_devices)
    
    for key,data in all_data.items():
        print('key  :',key,  end=' > ')
        print(data.__dict__)
    #print(p1.devices['24_e4000_usbttyUSB1'].__dict__)
    
    #gateway OPERATIONS################################################333
    


    g.insert_missing_devices(cur_devices)
    g.insert_missing_cmds()

    cur_devices = g.get_alias_db(cur_devices)
    for key,d in cur_devices.items():
        print(d.name)


    #leqid = g.give_eqLogic(list(p1.devices.values())[0])
    #cmdqid = g.give_eqLogic(list(p1.devices.values())[0])
    def history_process(cur_device, adata):
    #FOR ONE VALUE
        print("\n\n\n*******history process device {} data {}**********************".format(cur_device.name, adata))
        leqid = g.give_eqLogic(cur_device)
        if leqid is not None:
            cmdid = g.give_cmd(adata,cur_device, leqid)
            if cmdid is not None:
                g.insert_data_to_history(cur_device, adata, leqid, cmdid)
        print("*******************************************************************")
    
    
   

    
    for dev in cur_devices.values():
        for key,data in all_data.items():
            if str(dev.slave_id) == key.split('_')[0]:#1_e4000 in 1_e4000_pm1
                history_process(dev, data)
    

    #g.update_eqLogic("1_p4000_usbttyUSB0", 1 ,"new", None, None, None)
    #g.update_eqLogic("1_p4000_usbttyUSB0", 1 ,"li", None, None, 'kitchen')

    #g.insert_missing_devices(cur_devices)
  
            
        
    
    
    
    #gateway OPERATIONS################################################333

"""
leqid = g.give_eqLogic(cur_devices['1_p4000_usbttyUSB0'])
        if leqid is not None:
            cmdid = g.give_cmd(datas[0],cur_devices['1_p4000_usbttyUSB0'], leqid)
            if cmdid is not None:
                g.insert_data_to_history(cur_devices['1_p4000_usb
""" 


    
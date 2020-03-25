import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')#finds mysql
sys.path.append('/home/pi/.local/lib/python3.5/')

from scan import Scan
from db import Gateway_Database
from data import Data
from env import Env
from unik_id import UnikId
from db_device import Db_Devices

import configparser

def get_slaveids_from_session():

    p = configparser.ConfigParser()
    p.read(Env.sessionfile)
    toscan = []
    for k,v in p._sections.items():
        x = v['usb'] + ':' + v['slaveid']
        toscan.append(x)
    slaveids = ','.join(toscan)
    return slaveids


if __name__ == "__main__":
    ###setup#################################################
    g = Gateway_Database()
    Env.setup_env()
    ##########################################################

    ####GET DEVICES############################################
    s1 = None
    if len(sys.argv) == 1: 
        s1 = Scan(None)
    elif sys.argv[1] == 'session': 
        slaveids = get_slaveids_from_session()
        s1 = Scan(slaveids)
    else:                  
        s1 = Scan(sys.argv[1])
    
    s1.scan()
    devices = s1.devices
    #######################################################
    """
    print(devices)

    for x in devices.values():
        print(x.__dict__
    """
    ####GET UNIKIDS############################################NEEDS DB
    nb_devices = len(devices)
    unikids = UnikId()
    unikids.get_eqlogicid_per_dev(nb_devices, g)
    eqlogic_ids = unikids.eqlogic_id
    print("unik eqlogic id : ", eqlogic_ids)
    unikids.get_cmdids_per_dev(nb_devices, g)
    cmd_ids =  unikids.cmd_ids
    print("unik cmd ids : ", cmd_ids)
    ##########################################################


    ####GET data############################################>NEEDS DEVICES
    all_data = Data.device_all_reg_to_ini(devices)
    
    for key,data in all_data.items():
        print('key  :',key,  end=' > ')
        print(data.__dict__)
    ##########################################################


    ####FUSION############################################>NEEDS DEVICES 
    
    for x in devices.values():
        print(x.__dict__)

    dbd1 = Db_Devices()
    new_devs = dbd1.add_ids_to_devices(devices, eqlogic_ids)

    for x in new_devs.values():
        print(x.__dict__)

    new_alldatas = dbd1.add_ids_to_datas(all_data, cmd_ids)

    for k,x in new_alldatas.items():
        print('key', k)
        print(x.__dict__)
    
    def read(alldata):
        for k,x in alldata.items():
            print('key', k)
            print(x.__dict__)
    
    #read(new_alldatas)
    print('THETEST++++++++++++++++++++++++++++++++++++++')
    #dbd1.read(new_alldatas)
    dbdevs = dbd1.add_datas_to_devices(new_alldatas,  new_devs)

    for x in dbdevs.values():
        print(x.__dict__)
        for d in x.datas:
            print("x gives >", d.__dict__)

    #!!!repr error, id print adata, fetches class attr when first created , any added later gone solut print __dict__

    ##########################################################



    ####ADD TO DB####
    g.insert_all_dbdevs(dbdevs)


    ###########



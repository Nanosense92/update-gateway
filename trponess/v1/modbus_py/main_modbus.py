import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')#finds mysql
sys.path.append('/home/pi/.local/lib/python3.5/')

from scan import Scan
from db import Gateway_Database
from data import Data
from env import Env
from unik_id import UnikId
from db_device import Db_Devices
from datetime import datetime

import configparser

"""
def get_str_for_slaveids_from_session():
    #0:24,7:2
    p = configparser.ConfigParser()
    p.read(Env.sessionfile)
    toscan = []
    for k,v in p._sections.items():
        #print(dict(v.items()))
        if 'usb' in v.keys() and 'slaveid' in v.keys():
            x = v['usb'] + ':' + v['slaveid']
            toscan.append(x)
            print(x)
    slaveids = ','.join(toscan)
    
    return slaveids
"""

if __name__ == "__main__":
    ###setup#################################################
    g = Gateway_Database()
    Env.setup_env()
    ##########################################################

    ####GET DEVICES############################################
    print("SCAN".center(100, '#'))

    """
    s1 = None
    if len(sys.argv) == 1: 
        s1 = Scan(None)
    elif sys.argv[1] == 'session': 
        slaveids = get_str_for_slaveids_from_session()
        if slaveids == []:
            slaveids = None
        s1 = Scan(slaveids)
    else:                  
        s1 = Scan(sys.argv[1])
    """
    s1 = Scan() #will exit if session.ini missing info

    if s1.emptysession:

        with open(Env.userlogfile, 'a+') as userlog:
            date = Env.get_date()
            stri = 'SESSION of ' + date
            stri = stri.center(100, '#')
            print(stri , file=userlog)
            print("vous n'avez aucune sonde sur la ,page d'accueil, scan pour les trouver ou ajoutez les manuellement.", file=userlog)

            print(''.center(100, '#') , file=userlog)
        sys.exit(-1)

    s1.scan()
    devices = s1.devices

    notfound = s1.not_found
    

    print("".center(100, '#'))
    #######################################################
    
    #eq = g.fetch_table('eqLogic', 'logicalId')
    
    for d in devices.values():
        
        indb = None
        deqid = -1
        try:
            cur = g.exec_sql("SELECT id FROM eqLogic WHERE logicalId='" + d.slave_id + "'")
            deqid = cur.fetchone()[0]
            print(deqid)
            indb = True
        except Exception as e:
            print('!!!!!! ' , d.name , 'NOT IN DB : ', e)
            indb = False

        try:
            if indb == True:

                g.exec_sql("delete from eqLogic where logicalId='" + d.slave_id + "'")
                g.db.commit()
                g.insert_table('eqLogic', 'id,                  name,       logicalId,      generic_type,isEnable,isVisible,status,  tags, object_id', \
                                    [deqid    , d.name,d.slave_id,  d.type, d.isenable,   d.isvisible, 'modbus',d.parentobj_name,d.parentobj_id])    
            else:
                g.insert_table('eqLogic', '                  name,       logicalId,      generic_type,isEnable,isVisible,status,  tags, object_id', \
                                    [ d.name,d.slave_id,  d.type, d.isenable,   d.isvisible, 'modbus',d.parentobj_name,d.parentobj_id])    


        except Exception as e:
            print('!!!!!! error' , d.name , ': ', e)
            
            continue
            """
            try:
                g.exec_sql("UPDATE eqLogic SET isVisible={} WHERE logicalId='{}'".format(d.isvisible, d.slave_id))
                g.exec_sql("UPDATE eqLogic SET isEnable={} WHERE logicalId='{}'".format(d.isenable, d.slave_id))
                g.exec_sql("UPDATE eqLogic SET name='{}' WHERE logicalId='{}'".format(d.name, d.slave_id))
                g.exec_sql("UPDATE eqLogic SET object_id={} WHERE logicalId='{}'".format(d.parentobj_id, d.slave_id))
                
                g.db.commit()
            
            except Exception as e:
                print('FAILED TO UPDATE : ', e)
                continue
            """
   
    
    Data.put_in_db(devices, g)

    g.db.close()

    print('-----------------------END-------------------------')
    



    ####GET UNIKIDS############################################NEEDS DB
    """
    print("UNIK IDS".center(50, '#'))

    nb_devices = len(devices)
    unikids = UnikId()
    unikids.get_eqlogicid_per_dev(nb_devices, g)
    eqlogic_ids = unikids.eqlogic_id
    print("unik eqlogic id : ", eqlogic_ids)
    unikids.get_cmdids_per_dev(nb_devices, g)
    cmd_ids =  unikids.cmd_ids
    print("unik cmd ids : ", cmd_ids)

    print("".center(50, '#'))
    """
    ##########################################################
    

    ####GET data############################################>NEEDS DEVICES
    
    
    """
    print("DATA".center(50, '#'))
    for key,data in all_data.items():
        print('key  :',key,  end=' > ')
        print(data.__dict__)
    print("".center(50, '#'))
    """
    ##########################################################
      

    ####FUSION############################################>NEEDS DEVICES 
    """
    for x in devices.values():
        print(x.__dict__)

    dbd1 = Db_Devices()
    new_devs = dbd1.add_ids_to_devices(devices, eqlogic_ids)

    #for x in new_devs.values():
    #    print(x.__dict__)

    new_alldatas = dbd1.add_ids_to_datas(all_data, cmd_ids)

    #for k,x in new_alldatas.items():
    #    print('key', k)
    #    print(x.__dict__)
    
    
    #read(new_alldatas)
    
    #dbd1.read(new_alldatas)
    dbdevs = dbd1.add_datas_to_devices(new_alldatas,  new_devs)

    for x in dbdevs.values():
        print(x.__dict__)
        for d in x.datas:
            print("x gives >", d.__dict__)

    #!!!repr error, id print adata, fetches class attr when first created , any added later gone solut print __dict__
    """

    #################user_log################################
    """
    option = 'a+'
    with open(Env.userlogfile, 'r') as userlog:

        lines = userlog.readlines()
        if len(lines) > 1000:
            option = 'w+'

    with open(Env.userlogfile, option) as userlog:

        date = Env.get_date()
        stri = 'SESSION of ' + date
        stri = stri.center(100, '#')
        print(stri , file=userlog)



        print("...notfound", file=userlog)
        if notfound == {} or len(notfound) == 0:
            print("vide", file=userlog)
        for x in notfound:
            print("usb: {} id: {}".format(x[0], x[1]) , file=userlog)
        
        
        print("", file=userlog)
        print("", file=userlog)
        
        for x in dbdevs.values():

            print(">>>>>usb {} id {} machine {}".format(x.usb_name, x.slave_id, x.type) , file=userlog)
            for d in x.datas:
                print("type: {} val: {} unit: {}".format(d.name, d.val, d.unit) , file=userlog)
            print("" , file=userlog)
            print("" , file=userlog)
                #print("data gives >", d.__dict__)
            
            

        print(''.center(100, '#') , file=userlog)
    """
    ##########################################################
    


    ####ADD TO DB####
    """
    print("DB ACTIONS".center(50, '#'))
    g.exec_sql("DELETE FROM eqLogic where status='modbus'") #so enocean can co exist
    g.db.commit()


    g.insert_all_dbdevs(dbdevs)


    print("".center(50, '#'))
    """

    ###########



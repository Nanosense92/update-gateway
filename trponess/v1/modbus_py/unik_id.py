import random
import configparser
from env import Env
from datetime import datetime,timedelta
import time
#from db import db

#add security in case goes over max else infinite loop
class UnikId:

    def __init__(self):
        self.eqlogic_id = []
        self.cmd_ids = []

    def get_eqlogicid_per_dev(self, nb_devices, db_obj):
        for _ in range(nb_devices):
            self.get_eqlogicid(db_obj)

    def get_cmdids_per_dev(self, nb_devices, db_obj):
        for _ in range(nb_devices):
            self.get_cmdids(db_obj)

    def get_eqlogicid(self, db_obj):


        eqlogic_ids = db_obj.fetch_table('eqLogic', 'id')
        ceqlogic_ids = [x['id'] for x in eqlogic_ids]
        r = random.randrange(1, 2147483647)
        #print(ceqlogic_ids)
        while r in ceqlogic_ids or r in self.eqlogic_id:
            r = random.randrange(1, 2147483647)
        self.eqlogic_id.append(r)
        return r
    
    def get_cmdids(self, db_obj):
        pass
        
        eqlogic_ids = db_obj.fetch_table('cmd', 'id')
        ceqlogic_ids = [x['id'] for x in eqlogic_ids]
        r = random.randrange(1, 2147483647)
        #print(ceqlogic_ids)
        for _ in range(40):
            i = 0
            while r in ceqlogic_ids or r in self.cmd_ids:
                i += 1
                if i == 1000:
                    print('>>>>>>>>>>>db is full')
                    return None
                r = random.randrange(1, 2147483647)
            self.cmd_ids.append(r)
        
        #cmd_ids = []
    
    def timer(self, n, db_obj, devs):
        x = configparser.ConfigParser()
        x.read(Env.timerfile)

        print(x._sections)
        if len(x._sections) == 0:

            x.add_section('COV')
            x.set('COV', '1', '')
            x.add_section('C02')
            x.set('CO2', '2', '')
            x.add_section('TMP')
            x.set('TMP', '3', '')
            x.add_section('HUM_ABS')
            x.set('HUM_ABS', '4', '')
            x.add_section('HUM_REL')
            x.set('HUM_REL', '5', '')
            x.add_section('PM1')
            x.set('PM1', '6', '')
            x.add_section('PM2.5')
            x.set('PM2.5', '7', '')
            x.add_section('PM10')
            x.set('PM10', '8', '')


            epoch_now = int(time.time())
            datetime_now = datetime.fromtimestamp(epoch_now)
            dateadd = timedelta(minutes=n)
            d = str(datetime_now + dateadd)
            x.add_section('time')
            x.set('time', 'x', d)

            with open(Env.timerfile, 'w+') as f:
                x.write(f)

        epoch_now = int(time.time())
        datetime_now = datetime.fromtimestamp(epoch_now)
        strxtime = x._sections['time']['x']
        xtime = datetime.strptime(strxtime, '%Y-%m-%d %H:%M:%S')

        print('now is', datetime_now)
        if datetime_now >= xtime:
            print('TIMER! ', str(datetime_now), '>=', str(xtime), 'changing eqlogic ids')
            x._sections.clear()

            #STATIC
            x.add_section('COV')
            x.set('COV', '1', '')
            x.add_section('C02')
            x.set('CO2', '2', '')
            x.add_section('TMP')
            x.set('TMP', '3', '')
            x.add_section('HUM_ABS')
            x.set('HUM_ABS', '4', '')
            x.add_section('HUM_REL')
            x.set('HUM_REL', '5', '')
            x.add_section('PM1')
            x.set('PM1', '6', '')
            x.add_section('PM2.5')
            x.set('PM2.5', '7', '')
            x.add_section('PM10')
            x.set('PM10', '8', '')
            
            epoch_now = int(time.time())
            datetime_now = datetime.fromtimestamp(epoch_now)
            dateadd = timedelta(minutes=n)
            d = str(datetime_now + dateadd)
            x.add_section('time')
            x.set('time', 'x', d)

            for d in devs.values():
                x.add_section(d.name)
                eqid = self.get_eqlogicid(db_obj)    
                x.set(d.name, 'eqlogicid', str(eqid))
            
            

            """
            x.add_section('PRE')
            x.add_section('LUX')
            x.add_section('PM1')
            x.add_section('PM2.5')
            x.add_section('PM10')
            x.add_section('TC')
            """

            with open(Env.timerfile, 'w+') as f:
                x.write(f)
        
        return x._sections
        """
        eqids = []
        for n,s in x._sections.items():
            if n == 'time':
                continue
        """ 
            
            


        
        

        




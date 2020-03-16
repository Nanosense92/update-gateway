import random 
from data import *
from datetime import datetime
import global_funs

"""
    Virtual Database
"""
#append cache file

class History_Row:

    def __init__(self):
        self.cmd_id = None
        self.datetime = None
        self.value = None
    
class Cmd_Row:

    def __init__(self):
        self.id = None
        self.name = None
        self.eqLogic_id = None
        self.eqType = None
        self.logicalId = None
        self.value = None
        self.unite = None

class EqLogic_Row:

    def __init__(self):
       self.id = None
       self.logicalId = None
       self.name = None
       self.generic_type = None
       self.eqType_name = None

class Virtual_Database:
    """
        the goal is to do have a python 
        repr of the db, we can check compare.
        before exec sql cmds

        !uses db to communicate with sql

        1.probe.devices 
        2.real db 
    """
    def __init__(self, db):
        #rows
        self.db = db
        self.history_table = []
        self.eqLogic_table = []
        self.cmd_table = []

    def generate_id(self, table):
        
        self_table = None
        if table == 'cmd':
            self_table = self.cmd_table
        if table == 'eqLogic':
            self_table = self.eqLogic_table
        self_id = [r.id for r in self.eqLogic_table]

        db_table_ids = self.db.fetch_table(table, 'id')
        
        re = True
        while re:
            rand_id = random.randint(0,2147483646)
            if rand_id not in db_table_ids and \
               rand_id not in self_id:
               re = False

        return rand_id 
        

    def insert_devices(self, devices):
        for device_name, device in devices.items():
            self.insert_device_in_eqLogic_cmd(device)
            self.insert_vals_in_history(device)    


    def insert_device_in_eqLogic_cmd(self, device):
        """
            constant call only when device is unknowed
        """

        e = EqLogic_Row()
        e.id = self.generate_id('eqLogic')
        e.logicalId = device.slave_id
        e.name = device.name
        e.generic_type = device.type 
        e.eqType_name = 'modbus'
        self.eqLogic_table.append(e)

        if device.type  == 'e4000':
            a = ['CO2','Total','Temperature', 'Humidity']
            b = ['CONC::value', 'Total', 'TMP::value','HUM::value']
            u = ['ppm','mg/m3','C','%']

        if device.type  == 'p4000':
            a = ['PM1','PM2.5','PM10']
            b = ['PM1::value','PM2.5::value','PM10::value']
            u = ['mg/m3','mg/m3','mg/m3']

        for a,b,u in zip(a,b,u):
            c = Cmd_Row()
            c.name = a
            c.id = self.generate_id('cmd')
            c.eqLogic_id = e.id
            c.eqType = 'modbus'
            c.logicalId = b
            c.unite = u
            self.cmd_table.append(c)            

    def insert_vals_in_history(self, device):
        """
            dynamic is always called
        """
        data_lst = fetch_datas(device)
        for data,c in zip(data_lst,self.cmd_table):
            h = History_Row()
            h.datetime = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            h.cmd_id = c.id
            h.value = data.val
            self.history_table.append(h)

    def compare_with_db(self):
        #fields = ",".join(EqLogic_Row.__dict__.keys())
        """
            1.check if slave_id  in eqLogic
            ~not yet 2.check if slave_id  in cmd
        """
        missing = []
        db_eqLogic_tab = self.db.fetch_table('eqLogic', 'logicalId')#slave_id
        db_slave_ids = [row['logicalId'] for row in db_eqLogic_tab]
        
        print(db_slave_ids)
        

        for r in self.eqLogic_table:
            if r.id not in db_slave_ids:
                missing.append(r.id)
        print(missing)
        return missing

        #cmd_table = self.fetch_table('cmd', 'name,id,generic_type,eqType_name')
        #history_table = self.fetch_table('history')
        """
        print(fields)
        for l in eqLogic_table:
            for a,b in l.items():
                print(a, b, sep=":")
        """

    def insert_db(self):
        """
        """
        eqLogic_fields = ",".join(EqLogic_Row().__dict__.keys())
        cmd_fields = ",".join(Cmd_Row().__dict__.keys())
        eqLogic_table = self.db.fetch_table('eqLogic', eqLogic_fields)#slave_id
        cmd_table = self.db.fetch_table('cmd', cmd_fields)#slave_id

        e = EqLogic_Row()
        e.id = self.generate_id('eqLogic')
        e.logicalId = device.slave_id
        e.name = device.name
        e.generic_type = device.type 
        e.eqType_name = 'modbus'
        self.eqLogic_table.append(e)

        if device.type  == 'e4000':
            a = ['CO2','Total','Temperature', 'Humidity']
            b = ['CONC::value', 'Total', 'TMP::value','HUM::value']
            u = ['ppm','mg/m3','C','%']

        if device.type  == 'p4000':
            a = ['PM1','PM2.5','PM10']
            b = ['PM1::value','PM2.5::value','PM10::value']
            u = ['mg/m3','mg/m3','mg/m3']

        for a,b,u in zip(a,b,u):
            c = Cmd_Row()
            c.name = a
            c.id = self.generate_id('cmd')
            c.eqLogic_id = e.id
            c.eqType = 'modbus'
            c.logicalId = b
            c.unite = u
            self.cmd_table.append(c)            




        


    def __str__(self):
        print("VIRTUAL DATABASE".center(200, '/'))
        #print("EqLogic".center())
        #for row in self.EqLogic_Row:
        #    for field in row:
        #        print()
        print("EQLOGIC TABLE".center(20, '>'))
        for row in self.eqLogic_table:
            global_funs.print_class(row, 'eqLogic row','-')
        print('\n' * 3)
        print("CMD TABLE".center(20, '>'))
        for row in self.cmd_table:
            global_funs.print_class(row, 'cmd row','-')
        print('\n' * 3)
        print("HISTORY TABLE".center(20, '>'))
        for row in self.history_table:
            global_funs.print_class(row, 'history row','-')
        
        """
        print(self.eqLogic_table)
        print(self.cmd_table)
        print(self.history_table)
        """
        print("".center(200, '/'))

        return ""
    
    #load real db in a vdb to compare to python data
    def print_link_repr(self, title):
        print(title.center(200, '/'))
        link_table = [[]]
        for e in self.eqLogic_table:
            for c,h in zip(self.cmd_table, self.history_table):
                link_row = []    
                if e.id == c.eqLogic_id:
                    link_row.append(h.datetime)
                    link_row.append(e.name)
                    link_row.append(e.generic_type)
                    link_row.append(e.id)
                    link_row.append(c.id)
                    link_row.append(e.logicalId)
                    link_row.append(h.value)
                    link_row.append(c.unite)
                    link_table.append(link_row)
            link_table.append([])


        #table display
        link_fields = ['time', 'dev_name', 'dev_type', 'eqLogic_id', 'cmd_id', 'slave_id', 'val', 'unit']
        for l in link_fields:
            print("{: >20}".format(l), end='')

        for link_row in link_table:
            for l in link_row:
                print("{: >20}".format(l), end='')
            print()
            print("".center(200,'-'))
        

        print("".center(200, '/'))


            









        



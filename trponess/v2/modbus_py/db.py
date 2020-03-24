import sys
import mysql.connector
from datetime import datetime, timedelta
from time import sleep


class Gateway_Database:

    def __init__(self):
        self.host="localhost"
        self.port="3306"
        self.user="jeedom"
        self.passwd="85522aa27894d77"
        self.database="jeedom"
        
        try:
            self.db = mysql.connector.connect(
                                    host="localhost",
                                    port="3306",
                                    user="jeedom",
                                    passwd="85522aa27894d77",
                                    database="jeedom")
            
            self.cur = self.db.cursor()

        except Exception as e:
            print("Gateway Error : ", e)
            sys.exit(1)


    def update_eqLogic(self, slave_id,new_alias,isEnable, isVisible, roomplace):
        if isVisible is not None:
            self.exec_sql("UPDATE eqLogic SET isVisible={} WHERE logicalId={}".format(isVisible, slave_id))
        if isEnable is not None:
            self.exec_sql("UPDATE eqLogic SET isEnable={} WHERE logicalId={}".format(isEnable, slave_id))
        if roomplace is not None:
            self.exec_sql("UPDATE eqLogic SET tags='{}' WHERE logicalId={}".format(roomplace, slave_id))
        if new_alias is not None:
            self.exec_sql("UPDATE eqLogic SET name='{}' WHERE logicalId={}".format(new_alias, slave_id))
        self.db.commit()
        
    def delete_eqLogic(self, slave_id):
        self.exec_sql("DELETE FROM eqLogic WHERE logicalId={}".format(slave_id))
        self.db.commit()

    def insert_missing_devices(self, devices):
    
        for dev_name,device in devices.items():
            if not self.search_table('eqLogic', 'LogicalId', [device.slave_id]):
                self.insert_table('eqLogic', 'name,logicalId,generic_type,isEnable,isVisible,status,tags', \
                                            [device.name,device.slave_id,device.type,1,1,'modbus','roomplace'])
        else:    
            self.exec_sql("UPDATE eqLogic SET generic_type='{}' WHERE logicalId={}".format(device.type, device.slave_id))
            self.db.commit()



    def insert_missing_cmds(self):
        """ 
            each device (EqLogic) has values cmds associated to it we stock in table cmd
            cmds for e4000 are Temperature,Humidity,CO2,Total(COV)
                     p4000 are PM10, PM2.5, PM1 
            under the form : in CMD table eqLogic.id = 12, name=PM10 
                                          eqLogic.id = 12, name=PM2.5
    
            1.very simple test, if one of eqLogic values is in cmd 
            2.stocks in history if sucess
        """
        #eqLogic_table = self.fetch_eq()
        eqLogic_table = self.fetch_table("eqLogic", "id,name,generic_type")

        print(eqLogic_table)
        #check if everything is right
        for row in eqLogic_table:
                #row['id'] is slave_id
            if self.search_table('cmd', 'eqlogic_id', [row['id']]):
                self.exec_sql("DELETE FROM cmd WHERE eqLogic_id={}".format(row['id']))
                self.db.commit()
            #not self.search_table('cmd', 'eqType', [row['name']]):
            if row['generic_type'] == 'p4000':
                self.insert_table('cmd', 'name,eqType,logicalId,eqLogic_id', ['PM10',row['name'],'PM10::value',row['id']])
                self.insert_table('cmd', 'name,eqType,logicalId,eqLogic_id', ['PM2.5',row['name'],'PM2.5::value',row['id']])
                self.insert_table('cmd', 'name,eqType,logicalId,eqLogic_id', ['PM1',row['name'],'PM1::value',row['id']])

            elif row['generic_type'] == 'e4000':
                self.insert_table('cmd', 'name,eqType,logicalId,eqLogic_id', ['Temperature',row['name'],'TMP::value',row['id']])
                self.insert_table('cmd', 'name,eqType,logicalId,eqLogic_id', ['Humidity',row['name'],'HUM::value',row['id']])
                self.insert_table('cmd', 'name,eqType,logicalId,eqLogic_id', ['CO2',row['name'],'CONC::value',row['id']]) 
                self.insert_table('cmd', 'name,eqType,logicalId,eqLogic_id', ['Total',row['name'],'total',row['id']])

                            
    def fetch_eq(self):
        
        cmd = "SELECT * FROM {}".format("eqLogic")
        print(cmd)
        cur = self.exec_sql(cmd)
        #fields = fields.split(',')
        #data = [dict(zip(fields,row)) for row in cur.fetchall()]
        data = cur.fetchall()

        print(data)
        return data


    def fetch_table(self, table, fields='*'):
        """
            1.we want each row of the table that contains the fields
              in the form of a list containing a dict of field:val
              list[22]['name'] -> 'this'
              <SELECT name,id FROM cmd>
            2.prints sql command
        """
    
        cmd = "SELECT {} FROM {}".format(fields, table)
        print(cmd)
        cur = self.exec_sql(cmd)
        fields = fields.split(',')
        data = [dict(zip(fields,row)) for row in cur.fetchall()]
        return data
    
    def insert_table(self, table, fields:str, val:list):
        """
            1.<INSERT INTO cmd (name,id) VALUES (val,val)>
            2.prints sql command
        """
        cmd = self._sql_formatter(None, val)
        cmd='INSERT INTO {} ({}) VALUES ({})'.format(table,fields, cmd)
        print(cmd)
        self.exec_sql(cmd)
        self.db.commit()#in order to insert
    
    def search_table(self, table, fields:str, val:list, err_msg=None):
        """
           1.we want to know if data is in a table in the database
           it does if we get the list of data
           <SELECT * FROM cmd WHERE this=val AND that=val2>
           it don't if list is empty
           2.prints error message if no data
           3.print sql cmd
        """
        fields = fields.split(',')
        cmd = self._sql_formatter(fields, val)
        cmd = 'SELECT * FROM {} WHERE {}'.format(table,cmd)
        print('checking if exists in database : ',cmd, end='')
        cur = self.exec_sql(cmd)
        data = cur.fetchall()
        if data == []:
            print()
            return False
        if err_msg is not None:
            print('-> ', err_msg)
        else:
            print('-> already in table ', table)
        return True
    
    #leading underscore means private function, dont use this outside the class
    def _sql_formatter(self,fields, vals):
        """
            1.sql demands strings to be under the form 'str'
            we must convert string python to 'python'
            so format creates "name='python'"
            2.if we want to assign values we must have (f,f,f) VALUES (v,v,v)
            if checking values are present WHERE f=v AND f=v AND ...
        """
        nval = []
        for v in vals:
            if type(v) == str:
                v = "'" + v + "'"
            nval.append("{}".format(v))
        cmd = []
        if fields is not None:
            for f,v in zip(fields, nval):
                cmd.append("{}={}".format(f,v))
            cmd = " AND ".join(cmd)
        else: 
            cmd = ",".join(nval)
        return cmd
        
    def exec_sql(self,cmd):
        """
            when retreiving data from the db
            its stocked in the cur
        """
        cur = self.db.cursor()
        print("   SQL CMD LAUNCHED : " +  cmd)
        cur.execute(cmd)
        return cur
        
    #########################NEW###########################################################

    def insert_all_dbdevs(self, dbdevs):
        for d in dbdevs.values():
            self.insert_dbdev_in_eqlogic(d)
            self.insert_dbdevdatas_cmd_history(d)

    def insert_dbdev_in_eqlogic(self, dbdev):
        device = dbdev
        self.insert_table('eqLogic', 'id,                  name,       logicalId,      generic_type,isEnable,isVisible,status,  tags', \
                                     [device.eqlogic_id, device.name,device.slave_id,  device.type, 1,         1,      'modbus','roomplace'])               
    
    def insert_dbdevdatas_cmd_history(self, dbdev):
        print(dbdev.datas)
        datas = dbdev.datas
        for adata in datas:
            self.insert_table('cmd', 'id,name,eqType,logicalId,eqLogic_id,value', [adata.cmd_id,adata.name,dbdev.name,adata.name+'::value',dbdev.eqlogic_id,str(adata.val)+adata.unit])
            self.insert_table('history', 'cmd_id,datetime,value', [adata.cmd_id,adata.date,adata.val])






if __name__ == "__main__":

    #g = Gateway_Database()
    pass

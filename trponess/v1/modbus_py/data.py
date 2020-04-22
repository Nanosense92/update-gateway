from datetime import datetime,timedelta
import configparser
import time
from env import Env
import sys

class Data:

    def __init__(self, device_name, name, val, unit, date):
        self.device_name = device_name
        self.name = name
        self.val = val
        self.unit = unit
        self.date = date
        self.cmd_id = ""#configparser
    
    def __str__(self):
        return "date:{} name:{} val:{} unit:{}".format(self.date, self.name, self.val, self.unit)

    @staticmethod
    def put_in_db(devices, g):
        #all_data = dict()
        for d in devices.values():
            
            deqid = -1
            try:
                cur = g.exec_sql("SELECT id FROM eqLogic WHERE logicalId='" + d.slave_id + "'")
                deqid = cur.fetchone()[0]
                print('got dev eqid : ', deqid)
            except Exception as e:
                print('ERROR couldnt get eqid : ', e)
                continue


            #D PASSES ITS REGS TO PARSE DATAS THAT GIVES THEM PROPS
            datas = Data.parse_datas(d)
            print('----------------------------------for ', d.name, ' ---------------------------')
            for key,data in datas.items():
                print('FOR DEVICE ', d.name ,' FOR DATA : <', data.name,  ' ', data.val, ' ', data.unit, '>')
                #x = g.search_table('eqLogic', 'logicalId', [d.slave_id])
                #print(x)
               


                try:
                    
                    ids = [cmdid['id'] for cmdid in g.fetch_table('cmd', 'id')]
                    #print(ids)
                
                    effects = ['CO2','COVT','HUMABS','HUM','TMP','PM1','PM2.5','PM10', 'ATM']
                    #for i in range(1,len(effects) + 1):
                    #    print(i)
                    
                    for i in range(len(effects)):
                        if i + 1 not in ids:
                            g.insert_table('cmd', 'id,name,logicalId','generic_type' \
                                            [i + 1, effects[i], effects[i]+'::value'],'modbus')
                        """
                        else:
                            g.exec_sql("delete from cmd where id={}".format(i + 1))
                            g.db.commit()
                            g.insert_table('cmd', 'id,name,logicalId', \
                                            [i + 1, effects[i], effects[i]+'::value'])
                        """
                    
                    for i in range(len(effects)):
                        if data.name == effects[i]:
                            data_tag = data.name+'_'+str(data.val)+'_'+data.unit
                            g.exec_sql("UPDATE cmd SET eqLogic_id={},eqType='{}',value='{}' WHERE id={}".format( deqid, d.name,data_tag, i + 1))
                            g.insert_table('history', 'cmd_id,datetime,value', [i + 1,data.date,data.val])
                            time.sleep(1)#to avoid duplicate key in history  date with secs and cmd_id


                except Exception as e:
                    print('                                                                                                             ERROR FOR ',d.name, ':', e)
                    print()
                    print()
                    continue

                """

                if data.name == 'CO2':
                    g.exec_sql("UPDATE cmd SET eqLogic_id={} WHERE id={}".format(x, 1))
                    g.insert_table('history', 'cmd_id,datetime,value', [1,data.date,data.val])
                
                if data.name == 'COVT':
                    g.exec_sql("UPDATE cmd SET eqLogic_id={} WHERE id={}".format(x, 2))
                    g.insert_table('history', 'cmd_id,datetime,value', [2,data.date,data.val])
                
                if data.name == 'HUMABS':
                    g.exec_sql("UPDATE cmd SET eqLogic_id={} WHERE id={}".format(x, 4))
                    g.insert_table('history', 'cmd_id,datetime,value', [4,data.date,data.val])
                
                if data.name == 'HUM':
                    g.exec_sql("UPDATE cmd SET eqLogic_id={} WHERE id={}".format(x, 5))
                    g.insert_table('history', 'cmd_id,datetime,value', [5,data.date,data.val])
                
                if data.name == 'TMP':
                    g.exec_sql("UPDATE cmd SET eqLogic_id={} WHERE id={}".format(x, 3))
                    g.insert_table('history', 'cmd_id,datetime,value', [3,data.date,data.val])
                
                if data.name == 'PM1':
                    g.exec_sql("UPDATE cmd SET eqLogic_id={} WHERE id={}".format(x, 6))
                    g.insert_table('history', 'cmd_id,datetime,value', [6,data.date,data.val])
                
                if data.name == 'PM2.5':
                    g.exec_sql("UPDATE cmd SET eqLogic_id={} WHERE id={}".format(x, 7))
                    g.insert_table('history', 'cmd_id,datetime,value', [7,data.date,data.val])
                
                if data.name == 'PM10':
                    g.exec_sql("UPDATE cmd SET eqLogic_id={} WHERE id={}".format(x, 8))
                    g.insert_table('history', 'cmd_id,datetime,value', [8,data.date,data.val])
                
                if data.name == 'ATM':
                    g.exec_sql("UPDATE cmd SET eqLogic_id={} WHERE id={}".format(x, 9))
                    g.insert_table('history', 'cmd_id,datetime,value', [9,data.date,data.val])
                """


                #self.insert_table('cmd', 'id,name,eqType,logicalId,eqLogic_id,value', [adata.cmd_id,adata.name,dbdev.name,adata.name+'::value',dbdev.eqlogic_id,str(adata.val)+adata.unit])
                
                
                print('>>>>>', key, data)

                

                #all_data[key] = data
        #return all_data


    @staticmethod
    def parse_datas(device):
        datas = dict()
        date = Env.get_date()
        reg = device.registers
        ikey = device.name + '_'
        if device.type == 'p4000':
            datas[ikey + 'pm1'] = Data(device.name, 'PM1', reg[2] , 'mg/m3', date)
            datas[ikey + 'pm2.5'] = Data(device.name, 'PM2.5', reg[3] , 'mg/m3', date)
            datas[ikey + 'pm10'] = Data(device.name, 'PM10', reg[4] , 'mg/m3', date)
            
        if device.type == 'e4000':
            datas[ikey + 'CO2'] = Data(device.name, 'CO2', reg[2] , 'ppm', date)
            datas[ikey + 'Total'] = Data(device.name, 'COVT', reg[3]*10 , 'mg/m3', date)
            datas[ikey + 'Humidity'] = Data(device.name, 'HUM', reg[5] , '%', date)
            datas[ikey + 'Temperature'] = Data(device.name, 'TMP', reg[4]/10, 'C', date)
        
        if device.type == 'EP5000':

            print('reg for EP5000 before 16 conversion' + reg)
            try:
                bit_status = format(reg[2], '016b')
                print(bit_status)
            except Exception as e:
                print('REG 2 TO 016B FAILED : ', e)
                sys.exit(14)

            #check 3rd bit
            print(type(bit_status))
          

            if bit_status[-1] == '1':
                print("CO2 captor pres")
                datas[ikey + 'CO2'] = Data(device.name, 'CO2',     reg[5] , 'ppm', date)

            if bit_status[-2] == '1':
                print("COV captor pres")
                datas[ikey + 'COVT'] = Data(device.name, 'COVT', reg[6] , 'mg/m3', date)

            if bit_status[-3] == '1':
                print("Temp captor pres")
                datas[ikey + 'Temperature'] = Data(device.name, 'TMP', reg[7]/10, 'C', date)

            if bit_status[-4] == '1':
                print("Hum captor pres")
                datas[ikey + 'Rel_Humidity'] = Data(device.name, 'HUM', reg[8] , '%', date)
                datas[ikey + 'Abs_Humidity'] = Data(device.name, 'HUM_ABS', reg[9]/100 , 'g/m3', date) #erreur dans doc cest g/m3  reg / 100
            

            if bit_status[-5] == '1':
                print('PM1 captor pres')
                datas[ikey + 'pm1'] = Data(device.name, 'PM1',     reg[13] , 'mg/m3', date)

            if bit_status[-6] == '1':
                print('PM2.5 captor pres')
                datas[ikey + 'pm2.5'] = Data(device.name, 'PM2.5', reg[12] , 'mg/m3', date)
            
            if bit_status[-7] == '1':
                print('PM10 captor pres')
                datas[ikey + 'pm10'] = Data(device.name, 'PM10',   reg[11] , 'mg/m3', date)

            if bit_status[-8] == '1':
                print("pression captor pres")
                datas[ikey + 'Pression'] = Data(device.name, 'ATM', reg[10]/10 , 'pa', date)

            if bit_status[-9] == '1':
                print('Son captor pres')
                datas[ikey + 'Son_pic'] = Data(device.name, 'son_pic', reg[15], '-', date)
                datas[ikey + 'Son_moyen'] = Data(device.name, 'son_moy', reg[14], '-', date)
            if bit_status[-10] == '1':
                print('Lux captor pres')
                datas[ikey + 'Lux'] = Data(device.name, 'Lux', reg[16], '-', date)
            if bit_status[-11] == '1':
                print('Tcouleur captor pres')
                datas[ikey + 'Tcouleur'] = Data(device.name, 'T_couleur', reg[17], '-', date)
            if bit_status[-12] == '1':
                print('Scintillement captor pres')
                datas[ikey + 'Scintillement'] = Data(device.name, 'Scintillement', reg[18], '%', date)


        return datas

      


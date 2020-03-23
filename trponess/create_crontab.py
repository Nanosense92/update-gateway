import sys 
import os
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')#finds mysql
sys.path.append('/home/pi/.local/lib/python3.5/')
import mysql.connector
import modbus_py.db

"""
month = sys.argv[4]
day = sys.argv[3]
day_week = sys.argv[5]
hour = sys.argv[2]
min = sys.argv[1]

cmd = sys.argv[6]
"""

"""
times = dict()
for arg in sys.argv:
    sarg = arg.split('=')
    if len(sarg) == 2 and 'hour' in arg: times['hour'] = sarg[1]
    if len(sarg) == 2 and 'min' in arg: times['hour'] = sarg[1]
"""

g = modbus_py.db.Gateway_Database()
eq = g.fetch_table("eqLogic", "logicalid")
slaveids = []
for s in eq:
    print(s.items())
    v = s['logicalid']
    #print("db slaveid : ", v)
    slaveids.append(v)

slaveids = ','.join(slaveids)


crontab_line = sys.argv[1]
lines = []

with open('/etc/crontab', 'r') as f:
    lines = f.readlines()

for i in range(len(lines)):
    if "modbus_py/main.py" in lines[i]:
        del lines[i]

#print(crontab_line + " " + "sudo python3 /var/www/html/nanosense/modbus/jite/update-gateway/trponess/modbus_py/main.py " + slaveids)

with open('/etc/crontab', 'w+') as f:
    f.writelines(lines)
    f.write(crontab_line + " " + "sudo python3 /var/www/html/nanosense/modbus/jite/update-gateway/trponess/modbus_py/main.py " + slaveids + '\n')






import sys 
import os
import configparser
from env import Env

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
"""
p = configparser.ConfigParser()
p.read(Env.sessionfile)
toscan = []
tosca = ""
for k,v in p._sections.items():
    print(k)
    d = {'usb':v['usb'], 'slaveid':v['slaveid']}
    x = v['usb'] + ':' + v['slaveid']
    toscan.append(x)

print(toscan)
slaveids = ','.join(toscan)
print(slaveids)
"""

#print(crontab_line + " " + "sudo python3 /var/www/html/nanosense/modbus/jite/update-gateway/trponess/modbus_py/main.py " + slaveids)

crontab_line = sys.argv[1]
lines = []

print(crontab_line)

with open('/etc/crontab', 'r') as f:
    lines = f.readlines()

for i in range(len(lines)):
    if "main_modbus.py" in lines[i]:
        if lines[i][0] != '#': 
            lines[i] = '#' + lines[i][0:]

lines.append(crontab_line + " root sudo python3 " + Env.target + "/main_modbus.py session > " + Env.logfile + ' 2>' + Env.logfile + '\n')
print(*lines)


#print(crontab_line + " " + "sudo python3 /var/www/html/nanosense/modbus/jite/update-gateway/trponess/modbus_py/main.py " + slaveids)

with open('/etc/crontab', 'w+') as f:
    f.writelines(lines)
    


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

"""




import sys 
import os
import configparser
from env import Env


#print(crontab_line + " " + "sudo python3 /var/www/html/nanosense/modbus/jite/update-gateway/trponess/modbus_py/main.py " + slaveids)

crontab_line = sys.argv[1]
lines = []

print(crontab_line)

with open('/etc/crontab', 'r') as f:
    lines = f.readlines()

for i in range(len(lines)):
    if "main_modbus.py" in lines[i]:
        lines[i] = ""
        #if lines[i][0] != '#': 
        #    lines[i] = '#' + lines[i][0:]

lines.append(crontab_line + " root sudo python3 " + Env.target + "/main_modbus.py session > " + Env.logfile + ' 2>' + Env.logfile + '\n')
print(*lines)


#print(crontab_line + " " + "sudo python3 /var/www/html/nanosense/modbus/jite/update-gateway/trponess/modbus_py/main.py " + slaveids)

with open('/etc/crontab', 'w+') as f:
    f.writelines(lines)
    


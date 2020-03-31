import sys
import configparser
import random

from env import Env

"""
    takes one config   usb=1,7 slaveids=1-84

"""

p = configparser.ConfigParser()
i = str(random.randrange(0,10000))
p.add_section(i)
for arg in sys.argv[1:]:
    x = arg.split('=')
    print(x)

    """
    if ',' in x[1]:   vals = x[1].split(',')
    elif '-' in x[1]: vals = x[1].split('-')
    else:             vals = [x[0]]
    """

    p.set(i, x[0], x[1])


with open(Env.scanconfigfile, 'a+') as f:
    p.write(f)





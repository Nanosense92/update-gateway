#!/usr/bin/python3
# -*- coding: utf-8 -*-

import pymodbus
import logging
import time
import sys, getopt
import mysql.connector
from subprocess import PIPE, Popen
from pymodbus.repl.client import ModbusSerialClient as MbClient
from pymodbus.transaction import ModbusRtuFramer
from datetime import datetime
from reg_description import *

mode = "rtu" # default mode the modbus
max_id = 50 # default max id for the scan
cache_file_name = "cache_slaveid_probe.txt" # default cache file
pretty_print = False # default display of the data
interface = None # interface the user want to scan

def help():
    print("Usage: python3 " + __file__ + " [OPTION]...")
    print("Read registers in the probes actually connected to the gateway via the Modbus Protocol.")
    print("Possible options: ")
    print("     -h or --help                    display this help and exit")
    print("     --rtu                           modbus in rtu mode")
    print("     --ascii                         modbus in ascii mode")
    print("     -c FILE or --cache_file FILE    use the file 'FILE' as the cache file")
    print("                                     (if the file does not exist, it will be created and the ids will be scanned)")
    print("     -m NUM or --max_id NUM          maximum id for the scan (NUM must be > 0)")
    print("     -p or --pretty-print            display the data of the probes with a better form")
    print("     -i INTERFACE or ")
    print("     --interface INTERFACE           choose the interface to read the data instead of using all interfaces")

def main(argv):
    try:
        # handle several options
        opts, args = getopt.getopt(argv,
                "hi:pm:c:",
                ["help", "pretty-print", "interface=", "rtu", "ascii", "max_id=", "cache_file="])
    except getopt.GetoptError:
        print("This option doesn't exist or the syntax is wrong")
        print("Try 'python3 " + __file__ + " -h' or 'python3 " + __file__ + " --help' for more information.")
        sys.exit(1)
    for opt, val in opts:
        if (opt in ("-h", "--help")):
            # print help
            help()
            sys.exit(0)
        global mode
        if (opt == "--rtu"):
            # activate rtu mode for modbus
            mode = "rtu"
        if (opt == "--ascii"):
            # activate ascii mode for modbus
            mode = "ascii"
        if (opt in ("-m", "--max_id")):
            if (int(val) <= 0):
                print("The max id must be > 0")
                sys.exit(1)
            # set maximum id for the scan
            global max_id
            max_id = int(val)
        if (opt in ("-c", "--cache_file")):
            # set name of the cache file
            global cache_file_name
            cache_file_name = val
        if (opt in ("-p", "--pretty-print")):
            # activate pretty printing
            global pretty_print
            pretty_print = True
        if (opt in ("-i", "--interface")):
            # set interface to read the data of the probes
            global interface
            interface = val

if (__name__ == "__main__"):
    main(sys.argv[1:])

# debug logging
#logging.basicConfig(level=logging.DEBUG)

# dictionnary with the probes interface (/dev/ttyUSB0, ...) associated with their slave id
probes = {}

if (interface is None):
    # the user did not indicate an interface to read the data from
    try:
        # trying to open the cache file in read/write mode
        cache_file = open(cache_file_name, mode='r+')
    except:
        # the file does not exist, create it
        cache_file = open(cache_file_name, mode='w+')
    cache_file_lines = cache_file.readlines()
    nb_lines = 0

    # count lines of the cache file
    for line in cache_file_lines:
        nb_lines += 1

    # count the number of connected probe by checking their interfaces
    pipe_count = Popen('sudo find /dev/ -name "ttyUSB*" | wc -l', stdout=PIPE, shell=True)
    nb_probes = int(pipe_count.communicate()[0])

    # the number of lines in the cache file and the number of interface actually used are not the same, clear the cache file
    if (nb_lines != nb_probes):
        cache_file.write("")

    cache_file.close()

    # fill the dictionary with a null list (each interface can have multiple slave connecte
    pipe = Popen('sudo find /dev/ -name "ttyUSB*"', stdout=PIPE, shell=True)
    for line in pipe.stdout:
        probes[line.strip().decode("utf-8")] = []
else:
    # the user wants the data from the probes connected to the interface
    probes[interface] = []

# reopen the file to read the slave ids of the probes
try:
    cache_file = open(cache_file_name, mode='r')
except:
    cache_file = open(cache_file_name, mode='w+')
cache_file_lines = cache_file.readlines()

# iterate over the different interface of the probes
for probe in probes:

    # iterate over the line in the cache file
    for line in cache_file_lines:
        probe_f, slave_ids_comma = line.split()
        slave_ids = slave_ids_comma.split(',')

        # iterate over the different id for 
        for slave_id in slave_ids:
            # if the name of the probe is already in the cache file, save its id in the dictionary
            if (probe == probe_f):
                probes[probe].append(int(slave_id))
cache_file.close()
cache_file = open(cache_file_name, mode='a+')

# Main part: iterate over the different probes in the dictionary
for probe in probes:
    # we know the slave id (no need to scan all the possible ids)
    if (probes[probe] != []):
        print("Trying to read the different registers for the probes with the ids: " + str(probes[probe]) + " for " + probe + "...")
        # iterate over the known slave id (in the probes dictionary)
        for slave_id in probes[probe]:
            # connect to the probe using Modbus
            client = MbClient(method=mode, port=probe, stopbits=1, timeout=3, bytesize=8, parity="N", baudrate=9600)
            #client = MbClient(method='ascii'/'rtu', port=probe, stopbits=1, timeout=3, bytesize=8, parity="N", baudrate=9600)
            if (client.connect() == False):
                print("Connection failed: no probe for this interface")
                sys.exit(1)
            
            # read the 15 registers of the slaves starting with address 0x00
            # example registers for the E4000: [1, 15, 700, 0, 245, 50, 65535, 255, 65280, 20, 0, 15, 200, 300, 503]
            # example registers for the P4000: [0, 5, 0, 0, 0, 3, 5, 20, 204, 65280]
            response = client.read_input_registers(address=0x00, count=15, unit=slave_id)
            if ('registers' in response): #the request succeeded
                print("probe interface: " + probe + " - id: " + str(slave_id))
                print("-------------------------------------------------")
                if (len(response['registers']) == 15):
                    #E4000
                    if (pretty_print == True):
                        pretty_print_e4000(response['registers'])
                    else:
                        print(response['registers'])
                else:
                    #P4000
                    if (pretty_print == True):
                        pretty_print_p4000(response['registers'])
                    else:
                        print(response['registers'])
            client.close()
            time.sleep(1) # necessary to correctly empty the frame that contains the registers
    
    # we don't know the slave id so we need to scan over a range of possible id to find the correct ones
    else:
        print("Scanning the id from 0 to " + str(max_id) + " included for the probes connected to the interface " + probe + "...")
        first = True
        client = MbClient(method=mode, port=probe, stopbits=1, timeout=3, bytesize=8, parity="N", baudrate=9600)
        if (client.connect() == False):
            print("Connection failed: no probe for this interface")
            sys.exit(1)
        
        # iterate over the different possible slave id (by default: 0 - 50)
        for slave_id in range(0, max_id+1):
            print("Id " + str(slave_id) + "...")
            # same steps here except that we write the ids in a cache file for not having to scan again
            response = client.read_input_registers(address=0x00, count=15, unit=slave_id)
            if ('registers' in response):
                probes[probe].append(slave_id)
                if (first == True):
                    cache_file.write(probe + " " + str(slave_id))
                    first = False
                else:
                    cache_file.write("," + str(slave_id))
                
                print("probe interface: " + probe + " - id: " + str(slave_id))
                print("-------------------------------------------------")
                if (len(response['registers']) == 15):
                    if (pretty_print == True):
                        pretty_print_e4000(response['registers'])
                    else:
                        print(response['registers'])
                else:
                    if (pretty_print == True):
                        pretty_print_p4000(response['registers'])
                    else:
                        print(response['registers'])
            response = None
        client.close()
        if (first == False):
            cache_file.write("\n")

cache_file.close()

# push to db
db = mysql.connector.connect(
        host="localhost",
        port="3306",
        user="jeedom",
        passwd="85522aa27894d77",
        database="jeedom")
cursor = db.cursor()
#cursor.execute("SELECT eqLogic.name AS alias, eqLogic.logicalId, cmd.name, max(history.datetime), " +
#       "cmd.id FROM history, cmd, eqLogic WHERE history.log_id = cmd.id AND cmd.eqLogic_id = eqLogic.id " +
#       "GROUP BY cmd.id")

"""
cursor.execute("SELECT Id FROM eqLogic WHERE eqType_name='openenocean'")
lst = [str(data[0]) for data in cursor if data[0] != ""]
str_lst = ",".join(lst)
cmd = "SELECT Id FROM cmd WHERE eqLogic_Id IN ({})".format(str_lst)
cursor.execute(cmd)
lst = [str(data[0]) for data in cursor if data[0] != ""]
str_lst = ",".join(ls)
cmd = "INSERT INTO history(cmd_id) VALUES({})".format(str_lst)
print(cmd)
db.commit()
"""
"""
'Hello WOrld'

u = list(x)
print('id:', x[1], 'val', x[2], 'date:', x[3], sep=' ')    

log_id = x[1]
val = x[2]
date = x[3]


#print(str(date))
#print(type(log_id),type(val), type(str(date)))
mysql_cmd = "INSERT INTO history(log_id,datetime,value) VALUES('{}','{}','{}')".format(log_id, date, val)
mysql_check = "SELECT * FROM history WHERE value='{}'".format(val)
print(mysql_cmd)
print(mysql_check)


cursor.execute(mysql_cmd)
db.commit()
cursor.execute(mysql_check)
z = cursor.fetchall()
#for zi in z:
print(*z, sep='\n')
#cursor.execute("INSERT INTO history('log_id', 'datetime', 'value') VALUES('0', '1', '2')")
"""

"""
COMMAND
SELECT 
  eqLogic.  


"""


cursor.close()
db.close()


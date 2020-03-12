import sys
import re
import configparser
import os
import ast 
import getopt
from subprocess import PIPE, Popen


class Modbus_config:

    def __init__(self):
        self.mode = "rtu/ascii" # default mode the modbus
        self.max_id = 50 # default max id for the scan
        self.cache_file = 'modbus__cache/cache_file.ini' # default cache file
        self.pretty_print = False # default display of the data
        self.interface = 'all' # interface the user want to scan

    def help(self):
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

    def config(self, argv):
        try:
            # handle several options
            opts, args = getopt.getopt(argv,
                    "hi:pm:c:",
                    ["help", "pretty-print", "interface=", "rtu", "ascii", "max_id=", "cache_file="])
        except getopt.GetoptError:
            print("This option doesn't exist or the syntax is wrong")
            print("Try 'python3 " + __file__ + " -h' or 'python3 " + __file__ + " --help' for more information.")
            sys.exit(1)
        
        #create cache dir and default cache file
        if not os.path.isdir('modbus__cache'):
            os.mkdir('modbus__cache')
        open('modbus__cache/cache_file', mode='a+').close()

        for opt, val in opts:
            if (opt in ("-h", "--help")):
                # print help
                self.help()
                sys.exit(0)
            if (opt == "--rtu"):
                # activate rtu mode for modbus
                self.mode = "rtu"
            if (opt == "--ascii"):
                # activate ascii mode for modbus
                self.mode = "ascii"
            if (opt in ("-m", "--max_id")):
                if (int(val) <= 0):
                    print("The max id must be > 0")
                    sys.exit(1)
                # set maximum id for the scan
                self.max_id = int(val)

            if (opt in ("-c", "--cache_file")):
                # set name of the cache file
                if re.match("^[A-za-z0-9_]+$", val) is None:
                    print('Error : file name must be A-Z a-z 0-9 _')
                    sys.exit()
                val += '.ini'
                open('modbus__cache/' + val, mode='a+').close()
                self.cache_file = 'modbus__cache/' + val

            if (opt in ("-p", "--pretty-print")):
                # activate pretty printing
                self.pretty_print = True
            if (opt in ("-i", "--interface")):
                # set interface to read the data of the probes
                pipe = Popen('sudo find ' + val, stdout=PIPE,stderr=PIPE,shell=True)
                x = list(pipe.stdout)
                if len(x) == 0:
                    print("ERROR : interface: " + val + " don't exist")
                    print('connected usbs > sudo find /dev/ -name ttyUSB*')
                    pipe = Popen('sudo find /dev/ -name ttyUSB*', stdout=PIPE, shell=True)
                    usb = [line.strip().decode("utf-8")  for line in pipe.stdout]
                    print(*usb)
                    sys.exit(1)
                self.interface = val
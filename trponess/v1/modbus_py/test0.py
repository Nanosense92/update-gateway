#!/usr/bin/env python
"""
Pymodbus Client Framer Overload
--------------------------------------------------------------------------

All of the modbus clients are designed to have pluggable framers
so that the transport and protocol are decoupled. This allows a user
to define or plug in their custom protocols into existing transports
(like a binary framer over a serial connection).

It should be noted that although you are not limited to trying whatever
you would like, the library makes no gurantees that all framers with
all transports will produce predictable or correct results (for example
tcp transport with an RTU framer). However, please let us know of any
success cases that are not documented!
"""
# --------------------------------------------------------------------------- #
# import the modbus client and the framers
# --------------------------------------------------------------------------- #
import sys
sys.path.append('/home/pi/.local/lib/python3.5/site-packages/')
sys.path.append('/home/pi/.local/lib/python3.5/')
import sys
import pymodbus
#from pymodbus.client.sync import ModbusTcpClient as ModbusClient
from pymodbus.repl.client import ModbusSerialClient as MbClient
# --------------------------------------------------------------------------- #
# Import the modbus framer that you want
# --------------------------------------------------------------------------- #
# --------------------------------------------------------------------------- #
from pymodbus.transaction import ModbusSocketFramer as ModbusFramer
# from pymodbus.transaction import ModbusRtuFramer as ModbusFramer
#from pymodbus.transaction import ModbusBinaryFramer as ModbusFramer
#from pymodbus.transaction import ModbusAsciiFramer as ModbusFramer

# --------------------------------------------------------------------------- #
# configure the client logging
# --------------------------------------------------------------------------- #
import sys
import logging
logging.basicConfig()
log = logging.getLogger()
log.setLevel(logging.DEBUG)

if __name__ == "__main__":

    for id in sys.argv[1:]:
        # ----------------------------------------------------------------------- #
        # Initialize the client
        # ----------------------------------------------------------------------- #
        #client = ModbusClient('localhost', port='/dev/ttyUSB0', framer=ModbusFramer)
        print('testing id', id)

        #fc = pymodbus.transaction.ModbusRtuFramer()
        #fc.addToFrame("01 04 00 00 00 28 F0 14")


        client = MbClient(method='rtu', port='/dev/ttyUSB0', stopbits=1, timeout=3, bytesize=8, parity="N", baudrate=19200) 
        client.connect()

        #print(client.read_device_information(unit=int(id)))
        # ----------------------------------------------------------------------- #
        # perform your requests
        # ----------------------------------------------------------------------- #
        #rq = client.write_coil(1, True)
        print('-------------------------------')
        #rr = client.read_coils(1,1, unit=int(id))
        #rr = client.read_exception_status(unit=int(id))
        print('-------------------------------')
        #assert(not rq.isError())     # test that we are not an error
        #assert(rr.bits[0] == True)          # test the expected value
        res_rtu =  client.read_input_registers(address=0x00, count=40, unit=int(id))
        print(res_rtu)

        #crc=pymodbus.utilities.computeCRC('F014')
        #print(crc)

        # ----------------------------------------------------------------------- #
        # close the client
        # ---------------------------------------------------------------------- #
        client.close()

        print()
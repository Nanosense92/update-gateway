str = "0:7,1:24"

class Slave_id:

    def __init__(self, slaveid, usb):
        self.usb = usb
        self.slaveid = slaveid
    
class X:

    def __init__(self, slaveids_str):

        self.devices = dict()
        self.slaveids_lst = self.setup_slaveids(slaveids_str) #lst of Slave_id obj
        
    def setup_slaveids(self, slaveids_str):
        #0:12,1:7
        slaveids_lst = []
        print(slaveids_str)
        slaveids_pairs = slaveids_str.split(',')
        print(slaveids_lst)
        for slaveids_pair in slaveids_pairs:
            x = slaveids_pair.split(':')
            usb = x[0]
            slaveid = x[1]
            slaveids_lst.append(Slave_id(slaveid, usb)) 
        return slaveids_lst




print(str)
x = X(str)
for x in x.slaveids_lst:
    print(x.__dict__)


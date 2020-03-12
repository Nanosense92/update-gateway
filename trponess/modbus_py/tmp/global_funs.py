import inspect
from subprocess import PIPE, Popen

def print_class(iclass, header, sym, pall=False):
    """
        prints all self of class except self class types
    """
    print(header.center(50, sym))    
    for k,v in iclass.__dict__.items(): 
        if inspect.isclass(k) == False:
            print(k,v,sep='=')
    print(sym * 50)


def get_connected_usb():
    pipe = Popen('sudo find /dev/ -name "ttyUSB*"', stdout=PIPE, shell=True)
    usb = [line.strip().decode("utf-8") for line in pipe.stdout]
    return usb

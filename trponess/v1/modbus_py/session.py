from env import Env
import configparser
import sys
import random
import string
from shutil import copyfile

def session(option, args):
    """
        we store all devices in cache
        it deletes cache content
    
    """

    print('change SESSION')
    p = configparser.ConfigParser()
    p.read(Env.sessionfile)

    sn = args['name']

    print(p._sections)

    ch = ""
    if 'change' in args.keys():
        ch = args['change']

    if ch != "":
        if ch in p._sections.keys():
            p.remove_section(ch)
        del args['change']
    
    if sn in p._sections.keys():
        p.remove_section(sn)

    if option=='add':
        p.add_section(sn)
        p._sections[sn] = args.copy()


    print(p._sections)
    with open(Env.sessionfile,'w+') as cache_file:
        print('change SESSION')
        p.write(cache_file)


def randomStringDigits(stringLength=8):
    """Generate a random string of letters and digits """
    lettersAndDigits = string.ascii_letters + string.digits
    return ''.join(random.choice(lettersAndDigits) for i in range(stringLength))

if __name__ == '__main__':

    Env.setup_env()

    #copyfile(Env.sessionfile, Env.sessionfile + randomStringDigits())
    
    if len(sys.argv) < 3: print("not enough args") ; sys.exit(2)
    option = sys.argv[1]
    if option not in ['add','delete']: print("not add delete") ; sys.exit(3)


    args = dict()
    for arg in sys.argv[2:]:
        print(arg)
        x = arg.split('=')
        print(x)
        args[x[0]] = x[1]
        
    
    session(option, args)
        
    

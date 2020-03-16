import os

class Env:

    cachedir =        './modbus__cache/'
    notfoundfile =    './modbus__cache/notfound.ini'
    datafile =        './modbus__cache/data.ini'
    modbuscachefile = './modbus__cache/modbus_cache.ini'

    @staticmethod
    def create_dir(adir):
        if not os.path.isdir(adir):
            os.mkdir(adir)
            
    @staticmethod
    def create_file(afile):
        open(afile, mode='w+').close()

    @staticmethod
    def setup_env():
        Env.create_dir('./modbus__cache/')
        Env.create_file('./modbus__cache/modbus_cache.ini')
        Env.create_file('./modbus__cache/data.ini')
        Env.create_file('./modbus__cache/notfound.ini')

  
if __name__ == "__main__":

    Env.setup_env()
import os


class Env:

    target = '/var/www/html/nanosense/modbus/jite/update-gateway/trponess/modbus_py'

    cachedir =        target + '/modbus__cache/'
    notfoundfile =    target + '/modbus__cache/notfound.ini'
    datafile =        target + '/modbus__cache/data.ini'
    modbuscachefile = target + '/modbus__cache/modbus_cache.ini'

    @staticmethod
    def create_dir(adir):
        if not os.path.isdir(adir):
            os.mkdir(adir)
            
    @staticmethod
    def create_file(afile):
        open(afile, mode='w+').close()

    @staticmethod
    def setup_env():
        Env.create_dir(Env.cachedir)
        Env.create_file(Env.notfoundfile)
        Env.create_file(Env.datafile)
        Env.create_file(Env.modbuscachefile)

  
if __name__ == "__main__":

    Env.setup_env()
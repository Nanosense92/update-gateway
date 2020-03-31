import os


class Env:

    target = '/var/www/html/nanosense/modbus/jite/update-gateway/trponess/v1/modbus_py'

    cachedir =        target + '/modbus__cache/'
    notfoundfile =    target + '/modbus__cache/notfound.ini'
    datafile =        target + '/modbus__cache/data.ini'
    modbuscachefile = target + '/modbus__cache/modbus_cache.ini'
    logfile = target + '/modbus__cache/log.ini'
    sessionfile= target + '/modbus__cache/session.ini'
    userlogfile = target + '/modbus__cache/userlog.ini'
    deffile = target + '/modbus__cache/def.ini'
    scanconfigfile = target + '/modbus__cache/scan_config.ini'
    
    @staticmethod
    def create_dir(adir):
        if not os.path.isdir(adir):
            os.mkdir(adir)
            
    @staticmethod
    def create_file(afile, mode):
        open(afile, mode=mode).close()
        os.chmod(afile, 0o777)

    @staticmethod
    def setup_env():
        Env.create_dir(Env.cachedir)
        Env.create_file(Env.notfoundfile, '+w')
        Env.create_file(Env.datafile, '+w')
        Env.create_file(Env.modbuscachefile, '+w')
        Env.create_file(Env.logfile, '+w')
        Env.create_file(Env.sessionfile, '+a')
        Env.create_file(Env.userlogfile, '+a')
        Env.create_file(Env.deffile, '+a')
        Env.create_file(Env.scanconfigfile, '+a')

  
if __name__ == "__main__":

    Env.setup_env()
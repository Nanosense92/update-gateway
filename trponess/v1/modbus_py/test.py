
from datetime import datetime,timedelta
import time
import configparser
from env import Env

def timer(n):
        x = configparser.ConfigParser()
        x.read(Env.timerfile)

        print(x._sections)
        #if empty put def time

        if len(x._sections) == 0:
            epoch_now = int(time.time())
            datetime_now = datetime.fromtimestamp(epoch_now)
            dateadd = timedelta(minutes=n)
            d = str(datetime_now + dateadd)
            x.add_section('time')
            x.set('time', 'x', d)

            with open(Env.timerfile, 'w+') as f:
                x.write(f)
        
        epoch_now = int(time.time())
        datetime_now = datetime.fromtimestamp(epoch_now)
        strxtime = x._sections['time']['x']
        xtime = datetime.strptime(strxtime, '%Y-%m-%d %H:%M:%S')
        print(xtime)

        if datetime_now >= xtime:
            dateadd = timedelta(minutes=n)
            d = str(datetime_now + dateadd)
            x.set('time', 'x', d)

            with open(Env.timerfile, 'w+') as f:
                x.write(f)


timer(1)
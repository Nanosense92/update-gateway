conf = dict()
#conf['slaveid'] = "7,24 99-120 12-1 5,1"
conf['slaveid'] ='24'

usbs = None
slaveids = []

if conf['slaveid'] == 'all' : 
    slaveids = [i for i in range(1,255)] 
else:
    parts = conf['slaveid'].split(' ')#7,24 99-110 12-13 5,1
    print(parts)
    for part in parts:
        if ',' in part:
            eachdev = part.split(',')
            eachdev = list(map(int, eachdev))
            slaveids.extend(eachdev)
        if '-' in part:
            rang = part.split('-')
            rang = list(map(int, rang))
            rang = sorted(rang)#range returns [] if not
            newids = [i for i in range(rang[0],rang[1] + 1)] 
            slaveids.extend(newids)

print(slaveids)
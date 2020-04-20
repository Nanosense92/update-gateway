with open('/etc/crontab') as f:
    x = None
    l = f.readlines()
    for i in range(len(l)):
        if 'main_modbus' in l[i]:
            x = l[i]
            s = ""
            for z in x:
                if z == 'r':
                    break
                s += z

h = s.split(' ')
if '/' in h[1]:
    a = h[1].split('/')
    print('valeurs toutes les heures : ' + a[1])
if '/' in h[0]:
    a = h[0].split('/')
    print('valeurs toutes les minutes : ' + a[1])



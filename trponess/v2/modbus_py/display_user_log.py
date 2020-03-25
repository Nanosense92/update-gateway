from env import Env

with open(Env.userlogfile) as f:
    l = f.readlines()
    print(*l)
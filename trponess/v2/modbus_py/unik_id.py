import random
#from db import db

#add security in case goes over max else infinite loop
class UnikId:

    def __init__(self):
        self.eqlogic_id = []
        self.cmd_ids = []

    def get_eqlogicid_per_dev(self, nb_devices, db_obj):
        for _ in range(nb_devices):
            self.get_eqlogicid(db_obj)

    def get_cmdids_per_dev(self, nb_devices, db_obj):
        for _ in range(nb_devices):
            self.get_cmdids(db_obj)

    def get_eqlogicid(self, db_obj):
        eqlogic_ids = db_obj.fetch_table('eqLogic', 'id')
        ceqlogic_ids = [x['id'] for x in eqlogic_ids]
        r = random.randrange(1, 2147483648)
        #print(ceqlogic_ids)
        while r in ceqlogic_ids or r in self.eqlogic_id:
            r = random.randrange(1, 2147483648)
        self.eqlogic_id.append(r)
    
    def get_cmdids(self, db_obj):
        eqlogic_ids = db_obj.fetch_table('cmd', 'id')
        ceqlogic_ids = [x['id'] for x in eqlogic_ids]
        r = random.randrange(1, 2147483648)
        #print(ceqlogic_ids)
        for _ in range(4):
            while r in ceqlogic_ids or r in self.cmd_ids:
                r = random.randrange(1, 2147483648)
            self.cmd_ids.append(r)

        




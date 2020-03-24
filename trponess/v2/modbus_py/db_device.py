import random

class Db_Devices:
    """
        fusion of Scan, Unikid, Data classes
        unikids added to scan & data
        dataunikids added to scan
    """
    # devices, datas, eqlogic_id, cmd_ids
    def __init__(self):
        self.db_devices = None

        self.new_alldatas = None
        self.new_devices = None
    
    
    def add_ids_to_devices(self, devices, eqlogicids):
        ceqlogicids = eqlogicids.copy()
        for i,d in enumerate(devices.values()):
            #d.__dict__['eqlogic_id'] = ceqlogicids[i] #add new field new self.eqlogicid
            d.eqlogic_id = ceqlogicids[i]
        return devices
    
    def add_ids_to_datas(self, alldatas, cmdids):
        ccmdids = cmdids.copy()
        for i,d in enumerate(alldatas.values()):
            #d.__dict__['cmd_ids'] = ccmdids[i] #add new field new self.eqlogicid
            d.cmd_id = ccmdids[i]
        self.new_alldatas = alldatas.copy()
        return alldatas

    
    def add_datas_to_devices(self, x, devices):
        for d in devices.values():
            #print("\llll > ", d)
            d.datas = [] 
            for key,adata in x.items():
                #print(" adata key > ", key)
                #print(" adata val > ",adata.__dict__)
                print(key + "in " + d.name + " ?")
                if d.name in key:
                    #print("gonna add this .............", adata.cmd_ids)
                    d.datas.append(adata) 
        return devices
        





class Global:

    @staticmethod
    def get_n_key_dict(dict, n):
        try:
            return dict.values()[n]
        except:
            return None

UPDATE nanodb
SET 
    addr = 'https://data.pando2.io',
    port = '443',
    path = 'nsapi/measures'
WHERE
    addr = 'http://nsapi.pando2.fr';


sudo apt-get install python3-pip
pip3 install mysql-connector-python
pip3 install -U pymodbus
pip3 list
sudo mysql jeedom
CREATE USER 'jeedom'@'%' IDENTIFIED BY '85522aa27894d77';
GRANT ALL PRIVILEGES ON *.* TO 'jeedom'@'%' WITH GRANT OPTION;
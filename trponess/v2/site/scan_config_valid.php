<?php
#stocks in scan_config.ini
var_dump($_POST);
exec("sudo python3 ../modbus_py/add_a_scan_config 2>&1", $output, $return_value);
#loads from scan_config.ini
exec("sudo python3 ../modbus_py/main_modbus.py scan_config 2>&1", $output, $return_value);


?>
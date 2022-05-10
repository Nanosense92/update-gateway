<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'verify_conf_file.php';
require_once 'delete_and_set_remote_db.php';
require_once 'delete_jeedom_objects_and_set_new_ones.php';
require_once 'delete_enocean_pairing_and_set_new_ones.php';

$conf_file = "../uploads/import_jeedom_config.json";


if ( verify_conf_file($conf_file) === FALSE ) {
    echo "IMPORT FATAL ERROR : conf file '$conf_file' is not usable ... exiting\n";
    exit ;
}
echo "CONF FILE IS VERIFIED\n<br>";


if ( delete_remote_db_and_set_new_ones($conf_file) === FALSE ) {
    echo "IMPORT FATAL ERROR : delete_remote_db_and_set_new_ones() returned FALSE ... exiting\n";
    exit ;
}
echo "DELETE DATABASE AND SET NEW ONES : OK\n<br>";


if ( delete_jeedom_objects_and_set_new_ones($conf_file) === FALSE ) {
    echo "IMPORT FATAL ERROR : delete_jeedom_objects_and_set_new_ones() returned FALSE ... exiting\n";
    exit ;
}
echo "DELETE JEEDOM OBJ AND SET NEW ONES : OK\n<br>";

if ( delete_enocean_pairing_and_set_new_ones($conf_file) === FALSE ) {
	echo "IMPORT FATAL ERROR : delete_enocean_pairing_and_set_new_ones() returned FALSE ... exiting\n";
	exit ;
}
echo "PAIRING ENOCEAN : OK\n<br>";

echo "THE DOMOTIC GATEWAY IS NOW READY !\n<br>";


?>

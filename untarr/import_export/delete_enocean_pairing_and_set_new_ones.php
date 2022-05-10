<?php

require_once "/var/www/html/core/class/eqLogic.class.php";
require_once "./create_enocean_equipments.php";

function delete_enocean_pairing_and_set_new_ones($conf_file)
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require "/home/pi/enocean-gateway/get_database_password.php";

    $file_str = file_get_contents($conf_file);
    if ($file_str === FALSE) {
        echo "PAIRING ENOCEAN FATAL ERROR : file_get_contents() failed with file '$file' ... exiting\n";
        return FALSE ;
    }

    /* Get the file content (string) into a json object (assoc array) */
    $json = json_decode($file_str, $assoc = TRUE);
    if ( $json === NULL ) {
        echo "PAIRING ENOCEAN FATAL ERROR : json_decode() returned NULL\n";
        return FALSE ;
    }
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        echo "PAIRING ENOCEAN FATAL ERROR : json_last_error() shows an error for json_decode()\n";
        return FALSE ;
   	}

    /* Connect to database mysql */
    $db = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');
    if ($db === false || $db->connect_errno) {
        echo "PAIRING ENOCEAN FATAL ERROR : mysqli_connect() failed\n"; 
        return FALSE ;
    }

    $ret_query = $db->query('SELECT `id` FROM `eqLogic` WHERE `eqType_name`="openenocean"');
    if ($ret_query === false) {
        echo "PAIRING ENOCEAN FATAL ERROR : mysqli_query() failed\n";
        $db->close();
        return FALSE ;
    }

    $id_eqLogic = 0;
    while ( $id_eqLogic = $ret_query->fetch_array(MYSQLI_BOTH) ) {
       // echo "ID = " . $id_eqLogic[0] . "\n";
        $eqL = new eqLogic();
        $eqL->setId($id_eqLogic[0]);
        $eqL->remove();
        unset($eqL);
    }

    $db->close();

    for ($i = 0 ; $i < $json['number_of_equipment'] ; $i++) {
        $eep = $json['equipment'][$i]['eep'];
        $probe_model = $json['equipment'][$i]['probe_model'];
        $alias = $json['equipment'][$i]['alias'];
        $enocean_id = $json['equipment'][$i]['id'];
        $obj_name = $json['equipment'][$i]['object'];

        //echo ">> CREATE ENOCEAN EQUIPMENT $probe_model - $alias - $enocean_id\n";
        $ret = create_enocean_equipments($eep, $probe_model, $alias, $enocean_id, $obj_name);
        if ($ret === FALSE) {
            echo "PAIRING ENOCEANphp FATAL ERROR : An error occured while creating equipment or command\n";
            return FALSE;
        }
        sleep(5);
    }

    require "deactivate_jeedom_smoothing.php";
    deactivate_jeedom_smoothing();
    

   return TRUE;
}



?>


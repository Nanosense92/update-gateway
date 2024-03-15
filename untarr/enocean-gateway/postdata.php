<?php 
error_reporting (E_ALL ^ E_NOTICE);
/*
 * FILE : postdata.php
 */

include 'getsettings.php';
include 'httprequest.php';
include 'misc.php';

$logname = '/var/log/postdata.log';
$logfile = fopen($logname, 'a') or die('Cannot open file: ' . $logname . "\n");

////

// the error log file (contains all the IAQ data not sent)
$errorlogname = '/var/log/postdata_error.log';
$errorlogfile = fopen($errorlogname, 'a') or die('Cannot open file: ' . $errorlogname . "\n");//echo "** DEBUG - OFFSET = $offset\n";

// for automatic hour change --- SALAH EDDINE AIT ALLAOUA 13/03/2024 ---

// Exécute la commande shell pour obtenir les informations sur l'heure d'été/d'hiver pour l'année actuelle
$command = 'zdump -v Europe/Paris | grep $(date +%Y)';
$output = shell_exec($command);

// Divise la sortie en lignes
$lines = explode("\n", $output);

// Récupère les informations de la première et de la troisième ligne
$summer_time_info = explode(" ", $lines[1]); // Informations de la première ligne
$winter_time_info = explode(" ", $lines[3]); // Informations de la troisième ligne

// Récupère les dates de transition
$summer_date = strtotime($summer_time_info[2] . " " . $summer_time_info[3] . " " . $summer_time_info[4]);
$winter_date = strtotime($winter_time_info[2] . " " . $winter_time_info[3] . " " . $winter_time_info[4]);

// Obtient la date actuelle
$current_date = time();

// Vérifie si la date actuelle est en heure d'été ou d'hiver
$is_summer_time = ($current_date >= $summer_date && $current_date < $winter_date) ? 1 : 0;
// Affiche le résultat
echo $is_summer_time ? "Heure d'été\n" : "Heure d'hiver\n" ;

//--------END--------



/*
 * Query on the jeedom database that get, for each command (CO2, Temperature, PM, ...)
 * of each equipment, the last value saved in history 
 */
$offset = ($is_summer_time) ? "1:55:00" : "0:55:00";

echo "OFFSET = $offset\n";

$timezone_offset = 1;
echo "timezone offset = $timezone_offset\n";

// quand on récupère des données en modbus ou enocean, on a une valeur en bdd qui contient une des deux valeurs selon le mode utilisé
$TYPE_ENOCEAN = "openenocean";
$TYPE_MODBUS = "modbus";

$last_val_query = "SELECT eqLogic.name AS 'alias', eqLogic.logicalId, cmd.name, MAX(history.datetime) AS 'max_datetime',"
    . "cmd.id, cmd.eqType, object.name, eqLogic.object_id, eqLogic.configuration FROM cmd JOIN history ON history.cmd_id = cmd.id "
    . "JOIN eqLogic ON cmd.eqLogic_id = eqLogic.id "
    . "JOIN object ON object.id = eqLogic.object_id "
    . "WHERE datetime > ADDTIME(NOW(), '$offset') GROUP BY cmd.id";

//echo "\n\n============================\n$last_val_query\n\n";

$last_val_cmd_query = $dbconnect->query($last_val_query);

// echo "\nDEBUG ** LAST VAL CMD QUERY ---- BEGIN ----\n"; 
// print_r($last_val_cmd_query);
// echo "DEBUG ** LAST VAL CMD QUERY ----  END  ----\n";

/*
 * Formating the data to send a correct JSON file
 */
$table = array();
while ( $last_val_row = $last_val_cmd_query->fetch_array(MYSQLI_BOTH) ) {
    //fwrite($logfile, date('Y-m-d H:i:s') . "\t");
    $value_array = array();   
    
    //  echo "\nDEBUG ** LAST VAL ROW ---- BEGIN ----\n";
    //  print_r($last_val_row);
    //  echo "DEBUG ** LAST VAL ROW ----  END  ----\n";
    
    $equipment_alias = $last_val_row[0];
    $equipment_ID = $last_val_row[1];
    $command_name = $last_val_row[2];
    $command_ID = $last_val_row[4];
    $plugin_type = $last_val_row[5]; //modbus ou openocean
    $object_name = $last_val_row[6];
    $id_room = $last_val_row[7];
    $configuration = $last_val_row[8];
    
    if ($average_mode == 1) { // AVERAGE MODE        
        /* 
         * Query to get the average value of one cmd within the last
         * "$send_data_interval" minutes
         */
        $avg_val_query = "SELECT `cmd_id`, MAX(`datetime`) AS 'max_datetime', "
            . "AVG(`value`) AS 'average_value' FROM `history` WHERE "
            . "`cmd_id` = '$command_ID' AND `datetime` >= ADDTIME(NOW(), '$offset')";
        $avg_val_cmd_query = $dbconnect->query($avg_val_query);
        
        $avg_val_row = $avg_val_cmd_query->fetch_array(MYSQLI_BOTH);
        $last_datetime = $avg_val_row[1];
        $avg_value = $avg_val_row[2];        
        
        // Build the main array that will contain all the values for one pollutant
        $value_array[] = array(
            'at' => date('Y-m-d\TH:i:s\Z',
                strtotime($last_datetime . '-' . $timezone_offset . 'hours')),
            'value' => $avg_value
        );
        //var_dump($value_array);
   
    } // if () AVERAGE MODE
    else { // NORMAL MODE
        
        /*
         * Query to get all the values of one cmd within the last
         * "$send_data_interval" minutes
         */
        $val_query = "SELECT * FROM `history` WHERE "
            . "`cmd_id` = '$command_ID' AND "
            . "`datetime` >= ADDTIME(NOW(), '$offset')";
        $val_cmd_query = $dbconnect->query($val_query);
        
        while ( $val_row = $val_cmd_query->fetch_array(MYSQLI_BOTH) ) {
            $datetime = $val_row[1];
            $value = $val_row[2];
            
            // Build the main array that will contain all the values for one pollutant
            $value_array[] = array(
                'at' => date('Y-m-d\TH:i:s\Z',
                    strtotime($datetime . '-' . $timezone_offset . 'hours')),
                'value' => $value
            );
            //var_dump($value_array);
        }
    } // else NORMAL MODE
    
    
    # chopper l'ID du premier pere, dans $id_room
    /* $sql_query = "SELECT `object_id`, `configuration` FROM eqLogic WHERE eqLogic.logicalId = '$equipment_ID'";
    $result_cmd_query = $dbconnect->query($sql_query);
    $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
    //  echo "==========\n";  var_dump($val_row); echo "==========\n";
    $ret_json_decode = json_decode($val_row['configuration'], $assoc = TRUE);*/
    $ret_json_decode = json_decode($configuration, $assoc = TRUE);
    $eep = "";
    $usb_port = "";
    $modbus_address = "";
    if ($ret_json_decode != NULL && $plugin_type === $TYPE_ENOCEAN) {
        $eep = $ret_json_decode['device'];
    } else if ($ret_json_decode != NULL && $plugin_type === $TYPE_MODBUS) {
        $usb_port = $ret_json_decode['portserial'];
        $modbus_address=$ret_json_decode['unitID'];
    };
    # chopper l'alias du premier pere, dans $alias_room
    $sql_query = "SELECT name, father_id FROM object WHERE object.id = '$id_room'";
    $result_cmd_query = $dbconnect->query($sql_query);
    $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
    $alias_room = $val_row['name'];  
    
    # chopper l'ID du second pere, dans $id_floor
    // $sql_query = "SELECT father_id FROM object WHERE id = '$id_room'";
    // $result_cmd_query = $dbconnect->query($sql_query);
    // $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
    // $id_floor = $val_row['father_id'];   
    
   // $id_floor = $val_row['father_id'];

    //echo "DEBUGUATIONNNNNNNNNNNNNNNNN ;;;;  id_floor = $id_floor; ET name = $alias_room;  ;;;; \n";
   // exit;

    
    # chopper l'alias du second pere, dans $alias_floor
  //  $sql_query = "SELECT name, father_id FROM object WHERE object.id = '$id_floor'";
  //  $result_cmd_query = $dbconnect->query($sql_query);
  //  $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
   // $alias_floor = $val_row['name'];    
    
    # chopper l'ID du troisieme pere, dans $id_building
    // $sql_query = "SELECT father_id FROM object WHERE id = '$id_floor'";
    // $result_cmd_query = $dbconnect->query($sql_query);
    // $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
   // $id_building = $val_row['father_id'];    
    

    # chopper l'alias du troisieme pere, dans $alias_building
    //$sql_query = "SELECT name FROM object WHERE object.id = '$id_building'";
   // $result_cmd_query = $dbconnect->query($sql_query);
   // $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
   // $alias_building = $val_row['name'];    
    
    # chopper l'EEP
    // $sql_query = "SELECT configuration FROM eqLogic WHERE eqLogic.logicalID = '$equipment_ID'";
    // $result_cmd_query = $dbconnect->query($sql_query);
    // $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
    // $eep_before_parsing = $val_row['configuration'];
    
    $data_type = eep_traduction($eep, $equipment_alias); 
    $pollutant = ($plugin_type === $TYPE_ENOCEAN)? setpollutantEnOcean($command_name, $eep, $equipment_alias) : setpollutantModbus($command_name); // set the pollutant name
    $id = ($plugin_type === $TYPE_MODBUS)? createModbusId(gethostname(), $modbus_address, $usb_port) : $equipment_ID; // id enocean
    
    //fwrite($logfile, $equipment_alias . '-' . $pollutant . "\n");

    /* $sql_query = "SELECT status FROM eqLogic";
    $result_cmd_query = $dbconnect->query($sql_query);
    $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH); */

    // Build the header of the JSON
    $table['version'] = '1.0.0';
    $table['datastreams'] = array(
        array(
            'alias' => $equipment_alias . '-' . $pollutant,
            //'location' => $object_name,
            //'pollutant' => $command_name, 
            //'id' => $equipment_ID,
            'id' =>  $id,
            'probe_id' => ($plugin_type === $TYPE_MODBUS)? $id : generate_probe_id($equipment_ID, $data_type, $equipment_alias),
            'id_room' => $id_room,
            'alias_room' => $alias_room,
            //'id_floor' => $id_floor,
            //'alias_floor' => $alias_floor,
            //'id_building' => $id_building,
            //'alias_building' => $alias_building,
            'data_type' => ($plugin_type === $TYPE_ENOCEAN)? $data_type : "EP5000M",
            'data_field' => $pollutant,
            'number_of_values' => count($value_array),
            'environment' => determine_environment_using_alias($equipment_alias),
            'uid_gateway' => gethostname(),
            'datapoints' => $value_array
        )
    );
        
                
    // Encode the newly formatted table into a PHP json object
    $jsondata = json_encode($table, JSON_PRETTY_PRINT);
    
    if ( count($value_array) != 0 ) {
        http_request($dbconnect, $logfile, $jsondata, $equipment_alias, $pollutant, $errorlogfile);
    }
   // fgetc(STDIN);
} //// break

// close all the currently opened resources
//fclose($logfile);
//fclose($errorlogfile);
////

mysqli_close($dbconnect);

?>

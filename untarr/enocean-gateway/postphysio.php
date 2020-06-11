<?php
/*
 * FILE : postphysio.php
 */

include 'getsettings.php';
include 'httprequest.php';

// the main log file (contains all the successfully sent physiological impacts)
$logname = '/var/log/postphysio.log';
$logfile = fopen($logname, 'a') or die('Cannot open file: ' . $logname . "\n");
// the error log file (contains all the physiological impacts not sent)
$errorlogname = '/var/log/postphysio_error.log';
$errorlogfile = fopen($errorlogname, 'a') or die('Cannot open file: ' . $errorlogname . "\n");

/*
 * Query on the jeedom database to get all the distinct location from the impact
 * table
 */
$dist_loc_query = 'SELECT DISTINCT location FROM impact';
$dist_loc_cmd_query = $dbconnect->query($dist_loc_query);

/*
 * Formating the data to send a correct JSON file
 */
$table = array();
while ($dist_loc_row = $dist_loc_cmd_query->fetch_array(MYSQLI_BOTH)){
    for ($i = 3; $i < 8; $i++){
        $value_array = array();

        $location = $dist_loc_row[0];

        /*
         * Query to get all the values of one physiological impact within the last
         * "$send_data_interval" minutes
         */
        $val_query = "SELECT * FROM `impact` WHERE `location` = '$location' "
            . "AND `datetime` > ADDTIME(now(), '$offset')";
        $val_cmd_query = $dbconnect->query($val_query);

        fwrite($logfile, date('Y-m-d H:i:s') . "\t");
        fwrite($logfile, $location);

        while ($val_row = $val_cmd_query->fetch_array(MYSQLI_BOTH)){
            $datetime = $val_row[0];
            $value = $val_row[$i];

            // Build the main array that will contain all the values for one physio. impact
            $value_array[] = array(
                "at" => date("Y-m-d\TH:i:s\Z",
                    strtotime($datetime . '-' . $timezone_offset . 'hours')),
                "value" => $value
            );
            //var_dump($value_array);
        }

        /*
         * Query to get all the physio. effects name
         */
        $effect_name_query = "SELECT column_name FROM information_schema.columns "
            . "WHERE table_schema = 'jeedom' AND table_name = 'impact' limit 1 offset "
            . $i;
        $effect_name_cmd_query = $dbconnect->query($effect_name_query);
        $effect_name_row = $effect_name_cmd_query->fetch_array(MYSQLI_BOTH);
        $effect_name = $effect_name_row[0];



        // QUERIES


        # chopper l'ID du premier pere, dans $id_room
        $sql_query = "SELECT id FROM object WHERE name = \"$location\"";
        $result_cmd_query = $dbconnect->query($sql_query);
        $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
        //  echo "==========\n";  var_dump($val_row); echo "==========\n";
        $id_room = $val_row['id'];    
        

        
        # chopper l'alias du premier pere, dans $alias_room
        # chopper l'ID du second pere, dans $id_floor
        $sql_query = "SELECT name, father_id FROM object WHERE object.id = '$id_room'";
        $result_cmd_query = $dbconnect->query($sql_query);
        $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
        $alias_room = $val_row['name'];    
        $id_floor = $val_row['father_id'];
    
        # chopper l'alias du second pere, dans $alias_floor
        # chopper l'ID du troisieme pere, dans $id_building
        // $sql_query = "SELECT name, father_id FROM object WHERE object.id = '$id_floor'";
        // $result_cmd_query = $dbconnect->query($sql_query);
        // $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
        // $alias_floor = $val_row['name'];    
        // $id_building = $val_row['father_id']; 

        # chopper l'alias du troisieme pere, dans $alias_building
        // $sql_query = "SELECT name FROM object WHERE object.id = '$id_building'";
        // $result_cmd_query = $dbconnect->query($sql_query);
        // $val_row = $result_cmd_query->fetch_array(MYSQLI_BOTH);
        // $alias_building = $val_row['name'];    
        
        

        // Build the header of the JSON
        $table['version'] = "1.0.0";
        $table['datastreams'] = array(
            array(
                'alias' => $location . '-' . $effect_name,
                'id' => $id_room . '-' . gethostname(),
                //'id_room' => $id_room,
                //'alias_room' => $location,
               // 'id_floor' => $id_floor,
               // 'alias_floor' => $alias_floor,
               // 'id_building' => $id_building,
               // 'alias_building' => $alias_building,
                'data_type' => "physio",
                'data_field' => $effect_name,
                'number_of_values' => count($value_array),
                'environment' => ( stripos($alias_room, "QAA") === false ) ? "iaq" : "oaq",
                'uid_gateway' => gethostname(),
                ///// 'location' => $location,
                /////'pollutant' => $effect_name,
                /////'id' => '',
                'datapoints' => $value_array
            )
        );
        fwrite($logfile, '-' . $effect_name . "\n");

  //  echo "****** DEBUG\nPOSTPHYSIO JSON BEGIN ------\n"; print_r($table); echo "\nPOSTPHYSIO JSON END ------\n\n"; 
        
        // Encode the newly formatted table into a PHP json object
        $jsondata = json_encode($table, JSON_PRETTY_PRINT);

        // HTTP request with database query
        http_request($dbconnect, $logfile, $jsondata, $location, $effect_name,
            $errorlogfile);
    }
}

// close all the currently opened resources
fclose($logfile);
fclose($errorlogfile);
mysqli_close($dbconnect);



?>

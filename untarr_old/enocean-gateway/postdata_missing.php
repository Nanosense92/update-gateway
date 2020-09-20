<?php
/*
 * FILE : postdata_missing.php
 */

include 'getsettings.php';
include 'misc.php';

// the main log file (contains IAQ data not sent the second time)
$logname = '/var/log/postdata_missing.log';
$logfile = fopen($logname, 'a') or die('Cannot open file: ' . $logname . "\n");
// the error log file (contains IAQ data data not sent the first time)
$errorlogname = '/var/log/postdata_error.log';
$errorlogfile = fopen($errorlogname, 'r') or die('Cannot open file: ' . $errorlogname . "\n");

while(!feof($errorlogfile)){
    $filerow = fgets($errorlogfile);
    $pieces = explode(" ", $filerow);
    var_dump($pieces);

    $datetime = $pieces[0] . ' ' . $pieces[1];
    $alias = $pieces[2];
    $pollutant = $pieces[3];
    $url = $pieces[4];
    $httpcode = $pieces[5];

    if ($datetime && $httpcode){
        
        $date_format = date('Y-m-d H:i:s',
            strtotime($datetime . '+' . $timezone_offset . ' hours'));
        $command_name = r_setpollutant($pollutant);
        /*
         * Query on the history table of the jeedom database to get all the values
         * for one command
         */
        $id_query = $dbconnect->query("SELECT `eqLogic`.`name` AS 'alias', "
            . "`eqLogic`.`logicalId`, `cmd`.`name`, `history`.`datetime`, "
            . "`cmd`.`id` FROM `history`, `cmd`, `eqLogic` WHERE "
            . "`history`.`cmd_id` = `cmd`.`id` AND `cmd`.`eqLogic_id` = `eqLogic`.`id` "
            . "AND `eqLogic`.`name` = '" . $alias . "' AND `cmd`.`name` ='"
            . $command_name . "' GROUP BY `cmd`.`id`");
        /*
         * Formating the data to send a correct JSON file
         */
        $table = array();
        while ($id_row = $id_query->fetch_array(MYSQLI_BOTH)) {
            fwrite($logfile, date('Y-m-d H:i:s', strtotime($datetime)) . "\t");
            $value_array = array();

            $equipment_id = $id_row[1];
            $command_id = $id_row[4];
            
            fwrite($logfile, $alias . '-' . $command_name . "\n");
            $val_query = $dbconnect->query("SELECT * FROM `historyArch` WHERE `cmd_id` = '"
                . $command_id . "' AND datetime >= SUBTIME('" . $datetime . "', '00:01:00') "
                . "AND datetime <= ADDTIME('" . $datetime . "', '00:01:00')");
            
            while ($val_row = $val_query->fetch_array(MYSQLI_BOTH)){
                $datetime = $val_row[1];
                $value = $val_row[2];

                $value_array[] = array(
                    'at' => date('Y-m-d\TH:i:s\Z',
                        strtotime($datetime . '-' . $timezone_offset . 'hours')),
                    'value' => $value
                );
            }

            // Build the header of the JSON
            $table['version'] = '1.0.0';
            $table['datastreams'] = array(
                array(
                    'alias'=> $alias . '-' . $pollutant,
                    'location'=> '',
                    'pollutant'=> $command_name,
                    'id'=> $id_row[1],
                    'datapoints' => $value_array
                )
            );

            // Encode the newly formatted table into a PHP json object
            $jsondata = json_encode($table, JSON_PRETTY_PRINT);

            $url_array = explode(":", $url);
            $addr = $url_array[0] . ':' . $url_array[1];

            //http request with database query
            $http_res = $dbconnect->query("SELECT * FROM nanodb WHERE addr = '"
                . $addr . "'");

            while ($httpreq = $http_res->fetch_array(MYSQLI_BOTH)) {
                $Auth = $httpreq['location'];
                $table['datastreams'][0]['location'] = $httpreq['location'];
                $url = $httpreq['addr'];
                if ($httpreq['port'] != NULL)
                    $url = $url . ':' . $httpreq['port'];
                if ($httpreq['path'][0] != '/')
                    $url = $url . '/' . $httpreq['path'];
                else
                    $url = $url . $httpreq['path'];
                fwrite($logfile, $url . "\t");
                $ch = curl_init($pieces[4]);
                $login = $httpreq['login'];
                $pass = $httpreq['password'];
                //		var_dump($url);
                $jsondata = json_encode($table, JSON_PRETTY_PRINT);
                //		echo $alias . '-' . $command_name;
                //		echo "\n";
                #
                # Set the HTTP POST REQUEST with CURL
                #
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Token:'. ' ' . $Auth,
                ));
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_USERPWD, "$login:$pass");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);//debug
                $res = curl_exec($ch);
                //				echo $jsondata . "\n";
                fwrite($logfile, $res . "\n");
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpcode >= 400){
                    $errorlog = fopen($errorlogname, 'a');
                    fwrite($errorlog ,date('Y-m-d H:i:s'). ' ' . $alias . ' ' . $command_name .' '. $url . ' '. $httpcode. "\n");
                    fclose($errorlog);
                }
                if (curl_errno($ch))
                {
                    echo 'error:'. curl_error($ch);
                }
                curl_close($ch);
            }
        }
    }
}
fclose($backupfile);
fclose($logfile);
mysqli_close($dbconnect);
exec("sudo mv -f /home/pi/enocean-gateway/send-backup-script/getdata_error.log /home/pi");
?>

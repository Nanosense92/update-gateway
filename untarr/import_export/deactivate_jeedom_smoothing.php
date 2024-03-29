<?php

function deactivate_jeedom_smoothing()
{
    require "/home/pi/enocean-gateway/get_database_password.php";

    $hostname = 'localhost';
    $username = 'jeedom';
    $db = 'jeedom';

    $dbconnect = mysqli_connect($hostname, $username, $jeedom_db_passwd, $db);
    if ($dbconnect->connect_errno){
        printf('connection failed to database: ' . $db);
        return FALSE;
    }

    $logical_id_query = $dbconnect->query("SELECT `logicalId` FROM `cmd` WHERE `eqType` = 'openenocean'");
    while ($logical_id_line = $logical_id_query->fetch_array(MYSQLI_BOTH)) {
        $select_query = $dbconnect->query("SELECT `configuration` FROM `cmd` WHERE `logicalId` = '$logical_id_line[0]'");
        $config_row = $select_query->fetch_array(MYSQLI_BOTH);

        /* debug */
        //printf("configuration before modification:\n" . $config_row[0] . "\n\n");

        $json_object = json_decode($config_row[0], $options = JSON_OBJECT_AS_ARRAY);
        /* Possible values:
        * - none = no smoothing
        * - min = minimum smoothing
        * - max = maximum smoothing
        * - avg = average of each value every 5 minutes
        */
        $json_object["historizeMode"] = 'none';
        $new_config_row = json_encode($json_object);

        /* debug */
        //printf("configuration after modification:\n" . $new_config_row . "\n\n");

        $update_query = $dbconnect->query("UPDATE `cmd` SET `configuration` = '$new_config_row' WHERE `logicalId` = '$logical_id_line[0]'");
    }

    $dbconnect->close();
    return TRUE;
} // end func()

?>

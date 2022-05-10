<?php

require_once "/var/www/html/core/class/jeeObject.class.php";
require_once "/var/www/html/nanosense/import_export/find_jeedom_core_apikey.php";

function delete_jeedom_objects_and_set_new_ones($conf_file)
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require "/home/pi/enocean-gateway/get_database_password.php";

    /* Get file content in a string */
    $file_str = file_get_contents($conf_file);
    if ($file_str === FALSE) {
        echo "DELETE OBJ FATAL ERROR : file_get_contents() failed with file '$file' ... exiting\n";
        return FALSE ;
    }

    /* Get the file content (string) into a json object (assoc array) */
    $json = json_decode($file_str, $assoc = TRUE);
    if ( $json === NULL ) {
        echo "DELETE OBJ FATAL ERROR : json_decode() returned NULL\n";
        return FALSE ;
    }
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        echo "DELETE OBJ FATAL ERROR : json_last_error() shows an error for json_decode()\n";
        return FALSE ;
   	}

    /* Connect to database mysql */
    $db = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');
    if ($db === false || $db->connect_errno) {
        echo "DELETE OBJ FATAL ERROR : mysqli_connect() failed (0)\n"; 
        return FALSE ;
    }

    // QUERY TO FIND THE ID OF OBJ "NANOSENSE" BECAUSE WE DONT EANT TO DELETE IT
    $ret_query = $db->query('SELECT `id` FROM object WHERE name="Nanosense"');
    if ($ret_query === false) {
        echo "DELETE OBJ FATAL ERROR : mysqli_query() failed (1)\n";
        $db->close();
        return FALSE ;
    }
    $id_obj_nanosense = $ret_query->fetch_array(MYSQLI_BOTH);

    // QUERY TO FIND ALL OBJ IDs
    $ret_query = $db->query('SELECT `id` FROM object');
    if ($ret_query === false) {
        echo "DELETE OBJ FATAL ERROR : mysqli_query() failed (2)\n";
        $db->close();
        return FALSE ;
    }

    // DELETE OBJ
    $id_obj = 0;
    while ( $id_obj = $ret_query->fetch_array(MYSQLI_BOTH) ) {
        if ($id_obj !== $id_obj_nanosense) {
            $jee_obj = new jeeObject();
            $jee_obj->setId($id_obj[0]);
            $lol = $jee_obj->remove();
            unset($jee_obj);
        }
    }



    /* IP ADDRESS */
    $exec_output = array();
    $exec_ret = 0;
    exec("sudo /sbin/ifconfig | awk '/eth0/,/^$/' | grep -a 'inet ' | cut -d ' ' -f 10", $exec_output, $exec_ret);
    if ($exec_ret !== 0) {
        if ($fp !== false) {
            echo "DELETE OBJ FATAL ERROR : FAILED TO GET CURRENT PRIVATE IP ADDRESS\n";
            $db->close();
            return FALSE;
        }
    }
    $jeedom_ip = $exec_output[0];

    /* API KEY */
    //$ret_query = $db->query("SELECT `value` FROM `config` WHERE `plugin`='core' AND `key`='api'");
    //$jeedom_api_key = ($ret_query->fetch_array(MYSQLI_BOTH))[0];
    $jeedom_api_key = find_jeedom_core_apikey();

    for ($i = 0 ; $i < $json['number_of_objects'] ; $i++) {

//echo "CREATE OBJ FOR() i = $i et OBJ = '" . $json['objects'][$i]['name'] . "'\n";        
        if ($json['objects'][$i]['name'] === "Nanosense") {
            //echo ">>>>>>>>> CONTINUUUUUUUUE\n";
            continue ;
        }

        $father_id = false;
        if ( strlen($json['objects'][$i]['father']) > 0 ) {
            $ret_query = $db->query('SELECT `id` FROM `object` WHERE `name`="' . $json['objects'][$i]['father'] . '"');
            if ($ret_query === false) {
                echo "DELETE OBJ FATAL ERROR : mysqli_query() failed\n";
                $db->close();
                return FALSE ;
            }
            $father_id = ($ret_query->fetch_array(MYSQLI_BOTH))[0];
            if ( empty($father_id) ) {
                echo "DELETE OBJ FATAL ERROR : FATHER " . $json['objects'][$i]['father'] . " DOES NOT EXIST\n";
                $db->close();
                return FALSE;
            }
        }

        //echo "LOOP CREATE OBJ <" . $json['objects'][$i]['name'] . "> with father <" . $father_id . ">\n";
        

        $jsonrpc = new jsonrpcClient($jeedom_ip . '/core/api/jeeApi.php', $jeedom_api_key);

        if ( $jsonrpc->sendRequest('jeeObject::save', array('name' => $json['objects'][$i]['name'], 'isVisible' => 1, 
        'father_id' => $father_id)) ) {
            //echo "CREATED OBJ " . $json['objects'][$i]['name'] . " OK\n";
        }
        else {
            echo $jsonrpc->getError();
            mysqli_close($db);
            echo("DELETE OBJ FATAL ERROR: Failed to create obj using jeedom API\n");
            return FALSE;
        }

    } // end for()

    mysqli_close($db);

    return TRUE;
}


?>

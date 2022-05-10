<?php
require_once './jeeApi_class.php';
require_once "/var/www/html/nanosense/import_export/find_jeedom_core_apikey.php";


function create_enocean_equipments($eep, $probe_model, $alias, $enocean_id, $obj_name)
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    require "/home/pi/enocean-gateway/get_database_password.php";

    $cmd_ids_created = array();

    /* FIND IP ADDRESS using ifconfig and parsing ; note : better use ip than ifconfig (deprecated) ! */
    $exec_output = array();
    $exec_ret = 0;
    exec("sudo /sbin/ifconfig | awk '/eth0/,/^$/' | grep -a 'inet ' | cut -d ' ' -f 10", $exec_output, $exec_ret);
    if ($exec_ret !== 0) {
        echo("CREATE ENOCEAN EQUIPMENTS FATAL ERROR: FAILED TO GET CURRENT PRIVATE IP ADDRESS\n");
        return FALSE;
    }
    $jeedom_ip = $exec_output[0];

    /* Find Jeedom API key */
    $db = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');
    if ($db->connect_errno){
        echo("CREATE ENOCEAN EQUIPMENTS FATAL ERROR: FAILED TO CONNECT TO JEEDOM DATABASE\n");
        return FALSE;
    }
    // $ret_query = $db->query("SELECT `value` FROM `config` WHERE `plugin`='core' AND `key`='api'");
    // $jeedom_api_key = ($ret_query->fetch_array(MYSQLI_BOTH))[0];
    $jeedom_api_key = find_jeedom_core_apikey();

    /* Trouver l'id de l'objet */
    $ret_query = $db->query("SELECT `id` FROM `object` WHERE `name`=\"" . $obj_name . "\"");
    if ($ret_query === false) {
        echo("CREATE ENOCEAN EQUIPMENTS FATAL ERROR: FAILED TO GET OBJECT ID FROM DB\n");
        mysqli_close($db);
        return FALSE;
    }
    $object_id = "";
    $object_id = ($ret_query->fetch_array(MYSQLI_BOTH))[0];

    mysqli_close($db);  

    $id_equipment_created_1 = 0;
    $id_equipment_created_2 = 0;
    $id_equipment_created_3 = 0;
    $id_equipment_created_4 = 0;
    //$alias_to_create = "";
    //$eep = "";
    $iconModel = "";
    $conf_rorg = "";
    $conf_func = "";
    $conf_type = "";
    if ($probe_model === "E4000") { // profil CO2 ; le profil COV est crée plus tard dans ce code
        $iconModel = "a5-09/a5-09-04_nanosense_E4000_CO2";
    }
    else if ($probe_model === "P4000" || $probe_model === "EP5000" || $probe_model === "QAA") {
        $iconModel = "a5-09/a5-09-07_nanosense_p4000";
    }
    else if ($probe_model === "DOOR" || $probe_model === "WINDOW") {
        $iconModel = "d5-00/d5-00-01_nodon_ouverture_blanc";
    }
    else if ($probe_model === "OCCUPANCY") {
        $iconModel = "a5-07/a5-07-01_eosca_mouvement.jpg";
    }
    else {
        echo("Fatal Error creating jeedom equipment in enocean plugin (1)\n");
        return FALSE;
    }

    $exploded_eep = explode('-', $eep);
    $conf_rorg = $exploded_eep[0];
    $conf_func = $exploded_eep[1];
    $conf_type = $exploded_eep[2];

    
    // creation first profil (and only one for P4000 who has just one profil in enocean plugin)
    $jsonrpc = new jsonrpcClient( $jeedom_ip /*'http://82.64.155.200:60001*/ . '/core/api/jeeApi.php', $jeedom_api_key);
    if ( $jsonrpc->sendRequest('eqLogic::save', array(
        'eqType_name' => "openenocean",
        'name' => $alias/*_to_create*/, 
        'logicalId' => $enocean_id,
        'object_id' => $object_id,
        'isVisible' => 1,
        'isEnable' => 1,
        'configuration' => array('createtime' => '2021-01-01 11:22:33', 
                                //'actionid' => 'DEADBEEE',
                                'updatetime' => '2021-01-01 11:33:22',
                                'twoids' => 0,
                                'device' => $eep,
                                'iconModel' => $iconModel,
                                'rorg' => $conf_rorg,
                                'func' => $conf_func,
                                'type' => $conf_type,
                                'applyDevice' => $eep),
        'category' => array('heating' => 0, 
                            'security' => 0,
                            'energy' => 0, 
                            'light' => 0, 
                            'opening' => 0, 
                            'automatism' => 0, 
                            'multimedia' => 0, 
                            'default' => 0)
        )) ) {
        //print_r( $jsonrpc->getResult() );
        $id_equipment_created_1 = ($jsonrpc->getResult())['id'];
    }
    else {
        echo $jsonrpc->getError();
        echo("Fatal Error creating jeedom equipment in enocean plugin (2)\n");
        return FALSE;
    }


    require_once 'create_enocean_commands.php';
    $ret_func_create_commands = create_corresponding_jeedom_commands($jsonrpc, /*$alias,*/ $id_equipment_created_1, 0, 0, 0, $probe_model, $eep);

//echo "END FUNCTION CREATE EQUIPMENT ()\n"; 

    return $ret_func_create_commands;
    return TRUE; return TRUE; return TRUE; return TRUE; return TRUE; return TRUE; return TRUE; return TRUE;

    // creation second profil (needed for E4000 EP5000 QAA) ; it's a COV profil for these 3 probe models
    if ($probe_model === "E4000" || $probe_model === "EP5000" || $probe_model === "QAA") {
        //$alias_to_create = $alias . "-COV";
        //$eep = 'a5-09-05'; a5 09 0c ?
        $iconModel = "a5-09/a5-09-05_nanosense_E4000_VOC";
        $exploded_eep = explode('-', $eep);
        $conf_rorg = $exploded_eep[0];
        $conf_func = $exploded_eep[1];
        $conf_type = $exploded_eep[2];

        if ( $jsonrpc->sendRequest('eqLogic::save', array(
            'eqType_name' => "openenocean",
            'name' => $alias/*_to_create*/, 
            'logicalId' => $enocean_id, 
            'object_id' => $object_id,
            'isVisible' => 1,
            'isEnable' => 1,
            'configuration' => array('createtime' => '2021-01-01 11:22:33', 
                                    //'actionid' => 'DEADBEEE',
                                    'updatetime' => '2021-01-01 11:33:22',
                                    'twoids' => 0,
                                    'device' => $eep,
                                    'iconModel' => $iconModel,
                                    'rorg' => $conf_rorg,
                                    'func' => $conf_func,
                                    'type' => $conf_type,
                                    'applyDevice' => $eep),
            'category' => array('heating' => 0, 
                                'security' => 0,
                                'energy' => 0, 
                                'light' => 0, 
                                'opening' => 0, 
                                'automatism' => 0, 
                                'multimedia' => 0, 
                                'default' => 0)
            )) ) {
            //print_r( $jsonrpc->getResult() );
            $id_equipment_created_2 = ($jsonrpc->getResult())['id'];
        }
        else {
            echo $jsonrpc->getError();
            echo("Fatal Error creating jeedom equipment in enocean plugin (3)\n");
            return FALSE;
        }
    } // end if() creation second profil


    // creation third profil (needed for EP5000 QAA)
    if ($probe_model === "EP5000" || $probe_model === "QAA") {
        if ($probe_model === "EP5000") { // profil CO2
            //$alias_to_create = $alias . "-CO2";
            //$eep = 'a5-09-04';
            $iconModel = "a5-09/a5-09-04_nanosense_E4000_CO2";
            $exploded_eep = explode('-', $eep);
            $conf_rorg = $exploded_eep[0];
            $conf_func = $exploded_eep[1];
            $conf_type = $exploded_eep[2];
        }
        else if ($probe_model === "QAA") { // profil sound
            //$alias_to_create = $alias . "-SOUND";
            //$eep = 'a5-13-11';
            $iconModel = "a5-13/a5-13-11_a51311";
            $exploded_eep = explode('-', $eep);
            $conf_rorg = $exploded_eep[0];
            $conf_func = $exploded_eep[1];
            $conf_type = $exploded_eep[2];
        }
        else {
            echo("Fatal Error creating jeedom equipment in enocean plugin (4)\n");
            return FALSE;
        }

        if ( $jsonrpc->sendRequest('eqLogic::save', array(
            'eqType_name' => "openenocean",
            'name' => $alias/*_to_create*/, 
            'logicalId' => $enocean_id, 
            'object_id' => $object_id,
            'isVisible' => 1,
            'isEnable' => 1,
            'configuration' => array('createtime' => '2021-01-01 11:22:33', 
                                    //'actionid' => 'DEADBEEE',
                                    'updatetime' => '2021-01-01 11:33:22',
                                    'twoids' => 0,
                                    'device' => $eep,
                                    'iconModel' => $iconModel,
                                    'rorg' => $conf_rorg,
                                    'func' => $conf_func,
                                    'type' => $conf_type,
                                    'applyDevice' => $eep),
            'category' => array('heating' => 0, 
                                'security' => 0,
                                'energy' => 0, 
                                'light' => 0, 
                                'opening' => 0, 
                                'automatism' => 0, 
                                'multimedia' => 0, 
                                'default' => 0)
            )) ) {
            //print_r( $jsonrpc->getResult() );
            $id_equipment_created_3 = ($jsonrpc->getResult())['id'];
        }
        else {
            echo $jsonrpc->getError();
            echo("Fatal Error creating jeedom equipment in enocean plugin (5)\n");
            return FALSE;
        }
    } // end if() creation third profil


    // creation fourth profil (needed for QAA tmp hum)
    if ($probe_model === "QAA") {
        //$alias_to_create = $alias . "-TMP";
        //$eep = 'a5-04-03';
        $iconModel = "";
        $exploded_eep = explode('-', $eep);
        $conf_rorg = $exploded_eep[0];
        $conf_func = $exploded_eep[1];
        $conf_type = $exploded_eep[2];
        
        if ( $jsonrpc->sendRequest('eqLogic::save', array(
            'eqType_name' => "openenocean",
            'name' => $alias/*_to_create*/, 
            'logicalId' => $enocean_id, 
            'object_id' => $object_id,
            'isVisible' => 1,
            'isEnable' => 1,
            'configuration' => array('createtime' => '2021-01-01 11:22:33', 
                                    //'actionid' => 'DEADBEEE',
                                    'updatetime' => '2021-01-01 11:33:22',
                                    'twoids' => 0,
                                    'device' => $eep,
                                    'iconModel' => $iconModel,
                                    'rorg' => $conf_rorg,
                                    'func' => $conf_func,
                                    'type' => $conf_type,
                                    'applyDevice' => $eep),
            'category' => array('heating' => 0, 
                                'security' => 0,
                                'energy' => 0, 
                                'light' => 0, 
                                'opening' => 0, 
                                'automatism' => 0, 
                                'multimedia' => 0, 
                                'default' => 0)
            )) ) {
            //print_r( $jsonrpc->getResult() );
            $id_equipment_created_4 = ($jsonrpc->getResult())['id'];
        }
        else {
            echo $jsonrpc->getError();
            echo("Fatal Error creating jeedom equipment in enocean plugin (6)\n");
            return FALSE;
        }
    } // end if() creation fourth profil
    
   
    //require_once 'create_corresponding_jeedom_commands.php';
    //$cmd_ids_created = create_corresponding_jeedom_commands($jsonrpc, $id_equipment_created_1, $id_equipment_created_2, $id_equipment_created_3, $id_equipment_created_4, $probe_model);

    //return $cmd_ids_created;
    return TRUE; // TMP 
} // end function create_corresponding_jeedom_equipment()


?>

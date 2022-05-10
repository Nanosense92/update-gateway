<?php

// EP5000 P4000 E4000(cov et co2)
// QAA P4000 COV SON

function create_corresponding_jeedom_commands($jsonrpc, /*$alias,*/ $id_equipment_created_1, $id_equipment_created_2, $id_equipment_created_3, $id_equipment_created_4, $probe_model, $eep)
{
    $possible_eep = array(
        "d2-04-08", // E4000 CO2
        "a5-09-07", // P4000 QAA PM
        "a5-09-0c", // E4000 QAA COV
        "a5-13-11", // SOUND QAA
        "a5-04-03", // TMP QAA
        "d5-00-01", // DOOR/WINDOW 
        "a5-07-01", // OCCUPANCY 
        "a5-07-03", // LUX
        "a5-3f-7f", // GENERIC
        "a5-05-01", // ATM
        "a5-20-01"  // HEATER / COOLING
    );

    $eep_is_valid = FALSE;
    for ($i = 0 ; $i < count($possible_eep) ; $i++) {
        if ($eep === $possible_eep[$i]) {
            $eep_is_valid = TRUE;
            break ;
        }
    }

    if ($eep_is_valid === FALSE) {
        echo "CREATE COMMANDS FATAL ERROR: eep '$eep' is not valid\n";
        return FALSE;
    }

    $cmd_ids_created = array();

    $nb_cmd_to_create = 0;
    $name = "";
    $logicalId = "";
    $order = 0;
    $eqLogic_id = 0;
    $unite = "";
    $conf_id = "DEADBEED";

    if ($eep === "d2-04-08") {
        $nb_cmd_to_create = 4;
    }
    else if ($eep === "a5-09-07") {
        $nb_cmd_to_create = 4;
    }
    else if ($eep === "a5-09-0c") { // VOC
        $nb_cmd_to_create = 2; 
    }
    else if ($eep === "a5-13-11") { // SOUND
        $nb_cmd_to_create = 3;
    }
    else if ($eep === "a5-04-03") { // TMP QAA
        $nb_cmd_to_create = 3;
    }
    else if ($eep === "d5-00-01") { // WINDOW/DOOR
        $nb_cmd_to_create = 2;
    }
    else if ($eep === "a5-07-01") {
        $nb_cmd_to_create = 2;
    }
    else if ($eep === "a5-07-03") { // LUX
        $nb_cmd_to_create = 2;
    }
    else if ($eep === "a5-3f-7f") { // GENERIC
        $nb_cmd_to_create = 4;
    }
     else if ($eep === "a5-20-01") { // HEATER
        $nb_cmd_to_create = 3;
    }
    else if ($eep === "a5-05-01") { // ATM
        $nb_cmd_to_create = 2;
    }
    else {
        echo("Fatal Error creating jeedom command in enocean plugin (1)\n");
        return FALSE;
    }

//echo "BEGIN FUNCTION CREATE CMD () go create $nb_cmd_to_create commands\n";

// co2 (e4000 2 - ep5000 6) ; tmp (e4000 0 - ep5000 4) ; hum (e4000 1 - ep5000 5) ; 
// covtotal(e4000 3 - ep5000 3 - qaa 3) ; pm10 (p4000 0 - ep5000 0 - qaa 0) ; pm2_5 (p4000 1 - ep5000 1 - qaa 1) ;
//  pm1 (p4000 2 - ep5000 2 - qaa 2) ; son_average (qaa 4) ; son_peak (qaa 5)
    for ($i = 0 ; $i < $nb_cmd_to_create ; $i++) {
//echo "FOR() i = $i\n";
        $order = $i;
        $subtype = "numeric";
        $invertBinary = 0;
        if ($i === 0) {
            if ($eep === "d2-04-08") { // tmp
                $name = "Température";
                $logicalId = "TMP::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "°C";
            }
            else if ($eep === "a5-09-07") { // pm10
                $name = "PM10";
                $logicalId = "PM10::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "ug/m3";
            }
            else if ($eep === "a5-09-0c") { // total COV / NOX / O3 ... 
                $name = "Total";
                $logicalId = "total";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "ug/m3";
            }
            else if ($eep === "a5-13-11") { // average sound
                $name = "Average Sound level";
                $logicalId = "DBAA::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "dbA";
            }
            else if ($eep === "a5-04-03") { // 
                $name = "Température";
                $logicalId = "TMP::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "°C";
            }
            else if ($eep === "d5-00-01") { // DOOR/WINDOW
                $name = "Etat";
                $logicalId = "CO::raw_value";
                $subtype = "binary";
                $eqLogic_id = $id_equipment_created_1;
            }
            else if ($eep === "a5-07-01") { // OCCUPANCY
                $name = "Etat";
                $logicalId = "PIRS::raw_value";
                $subtype = "binary";
                $invertBinary = 1;
                $eqLogic_id = $id_equipment_created_1;  
            }
            else if ($eep === "a5-07-03") { // LUX
                $name = "Luminosity";
                $logicalId = "ILL::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "lx";
            }
            else if ($eep === "a5-3f-7f") {
                $name = "Part 1";
                $logicalId = "P1::value";
                $eqLogic_id = $id_equipment_created_1;
            }
            else if ($eep === "a5-20-01") {
                $name = "Ouverture";
                $logicalId = "CV::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "%";
            }
            else if ($eep === "a5-05-01") {
                $name = "Pression";
                $logicalId = "BAR::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "hPa";
            }
            else {
                echo("Fatal Error creating jeedom command in enocean plugin (4)\n");
                return FALSE;
            }
        } // end if i == 0
        else if ($i === 1) {
            if ($eep === "d2-04-08") { // hum
                $name = "Humidité";
                $logicalId = "HUM::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "%";
            }
            else if ($eep === "a5-09-07") { // pm2_5
                $name = "PM2.5";
                $logicalId = "PM2.5::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "ug/m3";
            }
            else if ($eep === "a5-09-07") { // pm2_5
                $name = "PM2.5";
                $logicalId = "PM2.5::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "ug/m3";
            }
            else if ($eep === "a5-09-0c" || $eep === "d5-00-01" // dbm
            || $eep === "a5-07-01" || $eep === "a5-07-03" || $eep === "a5-05-01") {
                $name = "dBm";
                $logicalId = "dBm";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "dbm";
            }
            else if ($eep === "a5-13-11") { // average sound
                $name = "Peak Sound Level";
                $logicalId = "DBAP::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "dbA";
            }
            else if ($eep === "a5-04-03") { // average sound
                $name = "Humidité";
                $logicalId = "HUM::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "°C";
            }
            else if ($eep === "a5-3f-7f") {
                $name = "Part 2";
                $logicalId = "P2::value";
                $eqLogic_id = $id_equipment_created_1;
            }
            else if ($eep === "a5-20-01") {
                $name = "Ouverture demandée";
                $logicalId = "ouverturedemandée";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "%";
            }
            else {
                echo("Fatal Error creating jeedom command in enocean plugin (5)\n");
                return FALSE;
            }
        } // end if i === 1
        else if ($i === 2) {
            if ($eep === "d2-04-08") { // co2
                $name = "CO2";
                $logicalId = "CO2::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "ppm";
            }
            else if ($eep === "a5-09-07") { // pm1
                $name = "PM1";
                $logicalId = "PM1::value";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "ug/m3";
            }
            else if ($eep === "a5-13-11" || $eep === "a5-04-03"
            || $eep === "a5-20-01") { 
                $name = "dBm";
                $logicalId = "dBm";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "dbm";
            }
            else if ($eep === "a5-3f-7f") {
                $name = "Part 3";
                $logicalId = "P3::value";
                $eqLogic_id = $id_equipment_created_1;
            }
            else {
                echo("Fatal Error creating jeedom command in enocean plugin (6)\n");
                return FALSE;
            }
        } // end if i === 2
        else if ($i === 3) {
            if ($eep === "a5-09-07" || $eep === "d2-04-08"
            || $eep === "a5-3f-7f") {
                $name = "dBm";
                $logicalId = "dBm";
                $eqLogic_id = $id_equipment_created_1;
                $unite = "dbm";
            }
            else {
                echo("Fatal Error creating jeedom command in enocean plugin (7)\n");
                return FALSE;
            }
        }
        

//echo "> CREATE CMD name='$name' - logicalID=$logicalId\n";

        if ( $jsonrpc->sendRequest('cmd::save', array(
            'name' => $name,
            'logicalId' => $logicalId,
            'eqType' => 'openenocean',
            'eqType_name' => 'openenocean',
            'order' => $order,
            'type' => 'info',
            'subType' => $subtype,
            'eqLogic_id' => $eqLogic_id,
            'isHistorized' => 1,
            'unite' => $unite,
            'configuration' => array('logicalId' => $logicalId,
                                    'id' => 'DEADBEED'),
            'template' => array('dashboard' => 'default', 'mobile' => 'default'),
            'display' => array('invertBinary' => $invertBinary),
            'isVisible' => 1
            )) ) {
            //print_r( $jsonrpc->getResult() );
            $cmd_ids_created[] = ($jsonrpc->getResult())['id'];
        }
        else {
            echo $jsonrpc->getError();
            echo("Fatal Error creating jeedom command in enocean plugin (2)\n");
            return FALSE;
        }

    } // end for()

//echo "END FUNCTION CREATE CMD ()\n";
    return TRUE;
    //return $cmd_ids_created;

} // end function create_corresponding_jeedom_commands()


?>

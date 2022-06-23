<?php
/*
 * FILE : misc.php
 *
 * DESCRIPTION : contains all the useful functions for the different scripts
 *
 */

function generate_probe_id($equipment_ID, $data_type, $alias) // ça trouve l'id base
{
    // Je ne sais pas du tout pourquoi il manque 13 et 18 ^^
    $decrement_for_base_id = array(
        'CO2' => 1,
        'VOC' => 2,
        'PM' => 3,
        'SOUND' => 4,
        'LUMINOSITY' => 5,
        'LIGHT_TMP_COLOR' => 6,
        'PRESSURE' => 7,
        
        'SULF_NOT_USED' => 8,
        'NOX_NOT_USED' => 9,
        'O3_NOT_USED' => 10,
        'FORMALDEHYDE_NOT_USED' => 11,
        'BENZENE_NOT_USED' => 12,
        
        'COMMANDS' => 0, // CAS PARTICULIER PCQ SINON ARRAY_KEY_EXISTS() RETOURNE FALSE
        'COMMANDS_DILU' => 14,
        'COMMANDS_RECYCL' => 15,

        'HEATER_NOT_USED' => 16,
        'COOLING_NOT_USED' => 17,

        'PHYSIOS_NOT_USED' => 19,
        'BUILDING_NOT_USED' => 20,
        'VIRUS_NOT_USED' => 21,

        'INDEXES' => 80, //0x50
        'COMMANDS' => 81
    );

    if ( array_key_exists($data_type, $decrement_for_base_id) === false ) {
        return $equipment_ID;
    }

    /* CAS PARTICUIER PCQ DILLUTION ET RECYCLAGE SONT TOUS DEUX "COMMANDS" MAIS ILS N'ONT PAS
    LA MEME INCREMENTATION ! */
    if ( $data_type === 'COMMANDS' ) {
        if ( stripos($alias, "DILU") !== false || stripos($alias, "VENTIL") !== false ) {
            $data_type = 'COMMANDS_DILU';
        }
        else if ( stripos($alias, "RECIR") !== false || stripos($alias, "RECYCL") !== false ) {
            $data_type = 'COMMANDS_RECYCL';
        }
    }

    $probe_id_decimal = intval($equipment_ID, 16) - intval($decrement_for_base_id[$data_type], 10);
    $probe_id_hexadecimal = dechex($probe_id_decimal);
    
    return strtoupper($probe_id_hexadecimal);
}

//echo generate_probe_id("FF81BA07", "PM");

function eep_traduction($eep, $eq_alias) // data_type
{
    if ($eep == 'd2-04-08')
        return 'CO2';
    if ($eep == 'a5-09-0c')
        return 'VOC';
    if ($eep == 'a5-09-07')
        return 'PM';
    if ($eep == 'a5-09-05')
        return 'VOC';
    if ($eep == 'a5-04-03')
        return 'TMP';
    if ($eep == 'a5-20-01')
        return 'HVAC';
    if ($eep == 'a5-07-01')
        return 'OCCUPANCY';
    if ($eep == 'd5-00-01')
        return 'OPENING';
    if ($eep == 'a5-13-11')
        return 'SOUND';
    if ( $eep == 'a5-3f-7f' && stripos($eq_alias, "Flow") !== false )
        return 'AIR_FLOW';
    if ($eep == 'a5-07-03')
        return 'LUMINOSITY';
    if ($eep == 'a5-05-01')
        return 'PRESSURE';
    if ( $eep == 'a5-3f-7f' 
        && (stripos($eq_alias, "DILU") !== false
        || stripos($eq_alias, "VENTIL") !== false
        || stripos($eq_alias, "RECIR") !== false
        || stripos($eq_alias, "RECYCL") !== false) ) {
        return 'COMMANDS';
    }
    if ( $eep == 'a5-3f-7f' && stripos($eq_alias, "LIGHT_TMP_COLOR") !== false )
        return 'LIGHT_TMP_COLOR';
    if ( $eep == 'a5-3f-7f' && stripos($eq_alias, "ATM") !== false )
        return 'PRESSURE';
    if ( $eep == 'a5-3f-7f' && stripos($eq_alias, "INDEXES") !== false )
        return 'INDEXES';
    if ( $eep == 'a5-3f-7f' && stripos($eq_alias, "COMMANDS") !== false )
        return 'COMMANDS';
    
    else
        return $eep;
}


// Rename the pollutant name 
function setpollutant($pollutant, $eep, $eq_alias) // data_field
{
    if ($pollutant == 'PM2.5')
        return 'PM2_5';
    if (strpos($pollutant, 'Temp') !== false)
        return 'TMP';
    if (strpos($pollutant, 'Hum') !== false)
        return 'HUM';
    if (strpos($pollutant, 'Total') !== false)
        return 'VOC';
    if ($eep === 'a5-07-01' && strpos($pollutant, 'Etat') !== false)
	    return 'OCCUPIED';
    if ($eep === 'd5-00-01' && strpos($pollutant, 'Etat') !== false && strpos($eq_alias, '-W') !== false)
        return 'WINDOW_CLOSED';
    if ($eep === 'd5-00-01' && strpos($pollutant, 'Etat') !== false && strpos($eq_alias, '-D') !== false)
        return 'DOOR_CLOSED';
    if (strpos($pollutant, 'Average Sound level') !== false)
        return 'DBAA';
    if (strpos($pollutant, 'Peak Sound Level') !== false)
        return 'DBAP';
    if (strpos($pollutant, 'Puissance 2') !== false)
        return 'POWER_2';
    if (strpos($pollutant, 'Puissance 3') !== false)
        return 'POWER_3';
    if (strpos($pollutant, 'Puissance 4') !== false)
	    return 'POWER_4';
    if (strpos($pollutant, 'Puissance') !== false)
	    return 'POWER_1';
    if (strpos($pollutant, 'Consommation 2') !== false)
        return 'CONSUMPTION_2';
    if (strpos($pollutant, 'Consommation 3') !== false)
        return 'CONSUMPTION_3';
    if (strpos($pollutant, 'Consommation 4') !== false)
        return 'CONSUMPTION_4';
    if (strpos($pollutant, 'Consommation') !== false)
        return 'CONSUMPTION_1';
    if ( $eep == 'a5-3f-7f' && stripos($eq_alias, "Flow") !== false && $pollutant !== "dBm" ) {
        if ( stripos($eq_alias, "IN") !== false )
            return 'AIR_FLOW_IN';
        if ( stripos($eq_alias, "OUT") !== false )
            return 'AIR_FLOW_OUT';
    }
    if ( strpos($pollutant, 'Luminosity') !== false )
        return 'LIGHT_LUX';
    if ( strpos($pollutant, 'Pression') !== false )
        return 'ATM';
    if ( $eep == 'a5-3f-7f' && strpos($pollutant, "Part 1") !== false
    && (stripos($eq_alias, "DILU") !== false || stripos($eq_alias, "VENTIL") !== false) ) {
        return 'VENTIL_LINEAR';
    }
    if ( $eep == 'a5-3f-7f' && strpos($pollutant, "Part 1") !== false
    && (stripos($eq_alias, "RECIR") !== false || stripos($eq_alias, "RECYCL") !== false) ) {
        return 'RECYCL_LINEAR';
    }
    if ( strcmp($pollutant, "Light_tmp_color") === 0 )
        return "LIGHT_TMP_COLOR";
    if ( strcmp($pollutant, "ventil_1_speed") === 0 )
        return "VENTIL_LOW_SPEED";
    if ( strcmp($pollutant, "ventil_2_speed") === 0 )
        return "VENTIL_HIGH_SPEED";
    if ( strcmp($pollutant, "ventil_linear") === 0 )
        return "VENTIL_LINEAR";
    if ( strcmp($pollutant, "recycl_1_speed") === 0 )
        return "RECYCL_LOW_SPEED";
    if ( strcmp($pollutant, "recycl_2_speed") === 0 )
        return "RECYCL_HIGH_SPEED";
    if ( strcmp($pollutant, "recycl_linear") === 0 )
        return "RECYCL_LINEAR";
    if ( strcmp($pollutant, "heater_percent") === 0 )
        return "HEATER_PERCENT";
    if ( strcmp($pollutant, "cooling_percent") === 0 )
        return "COOLING_PERCENT";
    if ( strcmp($pollutant, "cooling_OnOff") === 0 )
        return "COOOLING_BOOL";
    if ( strcmp($pollutant, "heater_OnOff") === 0 )
        return "HEATER_BOOL";
    if ( strcmp($pollutant, "atm") === 0 )
        return "ATM";
    
    return $pollutant;
}

// Translate the pollutant name to its real command name (in Jeedom)
function r_setpollutant($pollutant)
{
    if ($pollutant == 'PM2_5')
        return 'PM2.5';
    if ($pollutant == 'TMP')
        return 'Température';
    if ($pollutant == 'HUM')
        return 'Humidité';
    if ($pollutant == 'VOC')
        return 'Total';
    else
        return $pollutant;
}

function determine_environment_using_alias($equipment_alias)
{
    if ( stripos($equipment_alias, "QAA") === false ) {
        return "iaq";
    }

    // else
    return "oaq";
}


?>

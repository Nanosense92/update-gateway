<?php
/*
 * FILE : misc.php
 *
 * DESCRIPTION : contains all the useful functions for the different scripts
 *
 */

function eep_traduction($eep)
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
    
    else
        return $eep;
}


// Rename the pollutant name 
function setpollutant($pollutant, $eep, $eq_alias)
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
    // if (strpos($pollutant, 'Puissance 2') !== false)
    //     return 'POWER_2';
    // if (strpos($pollutant, 'Puissance 3') !== false)
    //     return 'POWER_3';
    // if (strpos($pollutant, 'Puissance 4') !== false)
	//     return 'POWER_4';
    // if (strpos($pollutant, 'Puissance') !== false)
	//     return 'POWER_1';
    // if (strpos($pollutant, 'Consommation 2') !== false)
    //     return 'CONSUMPTION_2';
    // if (strpos($pollutant, 'Consommation 3') !== false)
    //     return 'CONSUMPTION_3';
    // if (strpos($pollutant, 'Consommation 4') !== false)
    //     return 'CONSUMPTION_4';
    // if (strpos($pollutant, 'Consommation') !== false)
    //     return 'CONSUMPTION_1';
    else
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

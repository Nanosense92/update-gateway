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
    
    else
        return $eep;
}


// Rename the pollutant name in order to not exceed 5 characters
function setpollutant($pollutant, $eep)
{
    if ($pollutant == 'PM2.5')
        return 'PM2_5';
    if (strpos($pollutant, 'Temp') !== false)
        return 'TMP';
    if (strpos($pollutant, 'Hum') !== false)
        return 'HUM';
    if (strpos($pollutant, 'Total') !== false)
        return 'VOC';
    if (strpos($pollutant, 'COVT') !== false)
        return 'VOC';
    if ($eep === 'a5-07-01' && strpos($pollutant, 'Etat') !== false)
		return 'OCCUPIED';
	if ($eep === 'd5-00-01' && strpos($pollutant, 'Etat') !== false)
        return 'WINDOW_CLOSED';
    
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

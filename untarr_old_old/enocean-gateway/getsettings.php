<?php
/*
 * FILE : getsettings.php
 */

// read configuration file and store its content into a string
$json = file_get_contents('/home/pi/Nano-Setting.json');

// decode the string with json_decode and the assoc parameter activated 
// to store the decoded version into an array instead of a PHP object
$jsondecode = json_decode($json, $assoc = true);

// iterate over the different keys of the array and cast all the numeric values
// into integers
foreach($jsondecode AS $key => $value){
    switch($key){
        case 'timezone':
            // represents the offset between the actual gateway timezone offset (UTC+0)
            // and the Jeedom timezone offset (min = -12, max = 14)
            $timezone_offset = (int)$value; 
            break;
        case 'send-data-interval':
            // represents the period of time between 2 data sending
            // (min = 1 minute, max = 60 minutes)
            $send_data_interval = (int)$value;
            break;
        case 'average-mode':
            // enable average mode
            $average_mode = (int)$value;
            break;
    }
}

/* 3 cases for the offset that get the values in database (as the gateway internal timezone is UTC):
 *
 *
 *  -   If the timezone_offset (which must be the same as Jeedom) is positive,
 *      we must add timezone_offset - 1 hour(s) and 60 - send_data_interval minute(s)
 *      to the current datetime.
 *
 *      Ex: the actual datetime of the gateway is 2020-01-01 12:00:00,
 *      the timezone offset is 2 (so the actual datetime of the user is 2020-01-01 14:00:00)
 *      and the user wants to send the last 30 minutes of IAQ data, offset is equal to
 *      '1:30:00' so we can get all the values in the Jeedom database where the
 *      datetime is newer than now + 1:30:00 = 2020-01-01 13:30:00 (so the last 30 min of IAQ data).
 *
 *  -   If the timezone_offset is negative, we must add
 *      timezone_offset hour(s) and send_data_interval minute(s) to the current datetime.
 *
 *      Ex: the actual datetime of the gateway is 2020-01-01 12:00:00,
 *      the timezone offset is -5 (so the actual datetime of the user is 2020-01-01 07:00:00)
 *      and the user wants to send the last 15 minutes of IAQ data, offset is equal to
 *      '-5:45:00' so we can get all the values in the Jeedom database where the
 *      datetime is newer than now - 5:15:00 = 2020-01-01 06:45:00 (so the last 15 min of IAQ data).
 *
 *  -   If the timezone_offset if equal to 0, we must add -timezone_offset hour
 *      and send_data_interval minute(s) to the current datetime
 */   
if ($timezone_offset > 0){
    $offset = ($timezone_offset - 1) . ':' . (60 - $send_data_interval) . ':00';
}
else if ($timezone_offset < 0){
    $offset = $timezone_offset . ':' . $send_data_interval . ':00';
}
else{
    $offset = '-' . $timezone_offset . ':' . $send_data_interval . ':00';
}

/*
 * Database identification
 */
$hostname = 'localhost';
$username = 'jeedom';
$password = '85522aa27894d77';
$db = 'jeedom';

// Connect to the database
$dbconnect = mysqli_connect($hostname, $username, $password, $db);
if ($dbconnect->connect_errno){
    printf("Connection to '$db' database failed with this configuration");
    exit;
}
?>

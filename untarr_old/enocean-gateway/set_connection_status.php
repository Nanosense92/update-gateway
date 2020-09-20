<?php
/*
 * FILE : set_connection_status.php
 */

// read status connection file
$json = file_get_contents('/home/pi/enocean-gateway/connection_status.json');

// decode the string with json_decode and the assoc parameter activated
// to store the decoded version into an array instead of a PHP object
$jsondecode = json_decode($json, $assoc = true);

// initialize the parameters
$out = array();
$ret = 0;

// execute ping command with the IP of the router on which the gateway is actually
// connected to know if the gateway has an access to the Internet
exec("ping -q -w 1 -c 1 `ip r | grep default | cut -d ' ' -f 3` &>> /dev/null "
    . "&& echo 'ok' || echo 'error'", $output = &$out, $return_var = &$ret);

// DEBUG
/*
var_dump($out);
var_dump($ret);
 */
// !DEBUG

$date = date("Y-m-d H:i:s");

// change the isalive or isdead parameter (in the json array)
// according to the result of the ping command
if ($out[0] == "ok"){
    foreach($jsondecode AS $key => $value){
        if ($key == 'isalive')
            $jsondecode[$key] = $date;
    }
}
else{
    foreach($jsondecode AS $key => $value){
        if ($key == 'isdead')
            $jsondecode[$key] = $date;
    }
}

// create a new json string corresponding to the modified json array
$newjson = json_encode($jsondecode, JSON_PRETTY_PRINT);

// DEBUG
var_dump($newjson);
// !DEBUG

// write the new json string in the json file
file_put_contents('/home/pi/enocean-gateway/connection_status.json', $newjson . PHP_EOL);

?>

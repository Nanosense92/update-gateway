<?php
$hostname = 'localhost';
$username = 'jeedom';
$password = '85522aa27894d77';
$db = 'jeedom';


$dbconnect = mysqli_connect($hostname, $username, $password, $db);
if ($dbconnect->connect_errno){
    printf('Connection failed to database: ' . $db);
    exit;
}

$enable_rssi_commands_query = $dbconnect->query("UPDATE `cmd` SET `isHistorized` = 1, "
    . "`isVisible` = 1 WHERE `cmd`.`name` = 'dBm'");
if ($enable_rssi_commands_query == TRUE)
    printf('Historization of the RSSI of the EnOcean commands successfully activated'
        . "\n");
else
    printf('Error while trying to activate the historization of the RSSI: ' . "\n"
        . $dbconnect->error . "\n");

$dbconnect->close();

?>

<?php

require_once "/home/pi/enocean-gateway/get_database_password.php";

$hostname = 'localhost';
$username = 'jeedom';
$password = $jeedom_db_passwd;
$db = 'jeedom';

// Connect to the database
$dbconnect = mysqli_connect($hostname, $username, $password, $db);
if ($dbconnect->connect_errno){
    printf("Connection to '$db' database failed with this configuration");
    exit(1);
}

$http_query = $dbconnect->query('SELECT * FROM nanodb');

$login = "";
$pass = "";
$url = "";
$port = 0;
$path = "";
$token = "";

$fp = fopen("/var/www/html/nanosense/pushtocloud.conf", "w");
if ($fp === false) {
    echo "nanodb-to-file: Failed to fopen() pushtocloud.conf\n";
    exit(2);
}

if ($http_query !== false && $http_query !== true) {
    while ( $http_row = $http_query->fetch_array(MYSQLI_BOTH) ) {
        $token = $http_row['location'];
        $url = $http_row['addr'];
        $login = $http_row['login'];
        $pass = $http_row['password'];
        $path = $http_row['path'];
        $port = $http_row['port'];

        echo "WHILE = \n'$login'\n'$pass'\n'$url'\n'$port'\n'$path'\n'$token'\n\n";

        $str_infos_to_save = "'" . $login . "' " 
        . "'" . $pass . "' " 
        . "'" . $url . "' " 
        . "'" . $port . "' " 
        . "'" . $path . "' " 
        . "'" . $token . "'\n" ;
        if ( fwrite($fp, $str_infos_to_save) === false ) {
            echo "nanodb-to-file: Failed to fwrite() in pushtocloud.conf\n";
            fclose($fp);
            exit(3);
        }
    } // end while()
}// end if()

fclose($fp);


?>
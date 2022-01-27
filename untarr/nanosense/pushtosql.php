<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// initializing variables
$login = '';
$pass = '';
$addr = '';
$port = '';
$path = '';
$key = '';

// require_once "/home/pi/enocean-gateway/get_database_password.php";

// connect to the database
// $db = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');

// if($db->connect_errno){
//     echo 'connection to db failed'; 
//     exit; 
// }

// $create_table = 'CREATE TABLE IF NOT EXISTS
    // nanodb (ID INT AUTO_INCREMENT, login varchar(255), password varchar(255), addr varchar(255) NOT NULL, port varchar(255) NOT NULL, path varchar(255) NOT NULL, location varchar(255), PRIMARY KEY (ID))';

// $dblog = mysqli_query($db, $create_table);

// REGISTER USER
if ( isset($_POST['reg_db']) ) {
   // receive all input values from the form
    // $login = mysqli_real_escape_string($db, $_POST['log']);
    // $pass = mysqli_real_escape_string($db, $_POST['psw']);
    // $addr = mysqli_real_escape_string($db, $_POST['addr']);
    // $port = mysqli_real_escape_string($db, $_POST['port']);
    // $path = mysqli_real_escape_string($db, $_POST['path']);
    // $key = mysqli_real_escape_string($db,  $_POST['key']);

    $login = $_POST['log'];
    $pass  = $_POST['psw'];
    $addr  = $_POST['addr'];
    $port  = $_POST['port'];
    $path  = $_POST['path'];
    $key   = $_POST['key'];
}

if ($addr != '' && $port != '')
{
    // $insertquery = "INSERT INTO nanodb (login, password, addr, port, path, location) VALUES('$login', '$pass', '$addr', '$port', '$path', '$key')";
    // $dblog = mysqli_query($db, $insertquery);
    // $db->close();

    $fp = fopen("/var/www/html/nanosense/pushtocloud.conf", "a");
    if ($fp === false) {
        echo "FATAL ERROR: failed to open file where to save push infos\n";
        exit ;
    }
    $str_infos_to_save = "'" . $login . "' " 
    . "'" . $pass . "' " 
    . "'" . $addr . "' " 
    . "'" . $port . "' " 
    . "'" . $path . "' " 
    . "'" . $key . "'\n" ;
    if ( fwrite($fp, $str_infos_to_save) === false ) {
        echo "FATAL ERROR: failed to write file where to save push infos\n";
        fclose($fp);
        exit ;
    }

    fclose($fp);
    header('Location:main.php');
    exit ;
}
// $db->close();
?>

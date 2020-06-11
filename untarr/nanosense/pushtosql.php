<?php

// initializing variables
$login = '';
$pass = '';
$addr = '';
$port = '';
$path = '';
$key = '';

// connect to the database
$db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');

if($db->connect_errno){
    echo 'connection to db failed'; 
    exit; 
}

$create_table = 'CREATE TABLE IF NOT EXISTS
    nanodb (ID INT AUTO_INCREMENT, login varchar(255), password varchar(255), addr varchar(255) NOT NULL, port varchar(255) NOT NULL, path varchar(255) NOT NULL, location varchar(255), PRIMARY KEY (ID))';

$dblog = mysqli_query($db, $create_table);

// REGISTER USER
if (isset($_POST['reg_db'])) {
    // receive all input values from the form
    $login = mysqli_real_escape_string($db, $_POST['log']);
    $pass = mysqli_real_escape_string($db, $_POST['psw']);
    $addr = mysqli_real_escape_string($db, $_POST['addr']);
    $port = mysqli_real_escape_string($db, $_POST['port']);
    $path = mysqli_real_escape_string($db, $_POST['path']);
    $key = mysqli_real_escape_string($db, $_POST['key']);
}

if ($addr != '' && $path != '' && $port != '')
{
    $insertquery = "INSERT INTO nanodb (login, password, addr, port, path, location) VALUES('$login', '$pass', '$addr', '$port', '$path', '$key')";
    $dblog = mysqli_query($db, $insertquery);
    $db->close();
    header('Location:main.php');
    exit;
}
$db->close();
?>

<?php
//require_once "/home/pi/enocean-gateway/get_database_password.php";

if (isset($_POST['reg_db'])){
    // $db = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');
    // if ($db->connect_errno){
    //     echo 'connection to db failed';
    //     exit;
    // }

    // receive all input values from the form
    // $login = mysqli_real_escape_string($db, $_POST['log']);
    // $pass = mysqli_real_escape_string($db, $_POST['psw']);
    // $addr = mysqli_real_escape_string($db, $_POST['addr']);
    // $port = mysqli_real_escape_string($db, $_POST['port']);
    // $path = mysqli_real_escape_string($db, $_POST['path']);
    // $loc = mysqli_real_escape_string($db, $_POST['key']);

    $login = $_POST['log'];
    $pass  = $_POST['psw'];
    $addr  = $_POST['addr'];
    $port  = $_POST['port'];
    $path  = $_POST['path'];
    $loc   = $_POST['key'];

    if ($addr != '' && $port != ''){
        //$str = "DEBUG MODIFY LINE " . $db_id;// . " = " . $push_infos_array[$db_id] . "\n"; 
        //exec("sudo touch /home/pi/bonjour ; echo " . $push_infos_array[$db_id] . " | sudo tee /home/pi/bonjour");
        $fp = fopen("/var/www/html/nanosense/pushtocloud.conf", "w");
        if ($fp === false) {
            echo "FATAL ERROR: failed to open file where to save push infos (3)\n";
            exit ;
        }

        $str_infos_to_save = "'" . $login . "' " 
            . "'" . $pass . "' " 
            . "'" . $addr . "' " 
            . "'" . $port . "' " 
            . "'" . $path . "' " 
            . "'" . $loc . "'\n" ;

//exec("echo $db_id | sudo tee /home/pi/bonjour");
//exec("echo " . $push_infos_array[$db_id] . "\"\n\" | sudo tee -a /home/pi/bonjour");
        for ($i = 0 ; $i < count($push_infos_array) ; ++$i) {
//exec("echo for $i | sudo tee -a /home/pi/bonjour");
            if ($i == $db_id) {
//exec("echo if $i | sudo tee -a /home/pi/bonjour");
                if ( fwrite($fp, $str_infos_to_save) === false ) {
                    echo "FATAL ERROR: failed to write file where to save push infos(1)\n";
                    fclose($fp);
                    exit ;
                }
            }
            else {
//exec("echo else $i | sudo tee -a /home/pi/bonjour");
                if ( fwrite($fp, $push_infos_array[$i] . "\n") === false ) {
                    echo "FATAL ERROR: failed to write file where to save push infos(2)\n";
                    fclose($fp);
                    exit ;
                }
            }
        }
        fclose($fp);

        
        // $modify_table = "UPDATE nanodb SET login = '$login', password = '$pass', addr = '$addr', port = '$port', path = '$path', location = '$loc' WHERE id = '$db_id'";
        // $dbres = $db->query($modify_table);
        // $db->close();
        header('Location:main.php');
        exit;
    }
    // $db->close();
}
?>

<?php
/*
 * script to delete line in nanodb which contain reference for http post
 * */

//require_once "/home/pi/enocean-gateway/get_database_password.php";

$id = '';

if (isset($_POST['db_id2'])){
    //$db = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');// connect to the db
    // if($db->connect_errno){
    //     echo 'connection to db failed'; 
    //     exit;
    // }
    
    $id = /*mysqli_real_escape_string($db,*/ $_POST['db_id2']; //);
    //var_dump($id);
    if ($id != ''){
        $push_infos_array = file("/var/www/html/nanosense/pushtocloud.conf", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($push_infos_array === false) {
            echo "FATAL ERROR: failed to open file pushtocloud.conf (6)\n";
            exit ;
        }
        
        $fp = fopen("/var/www/html/nanosense/pushtocloud.conf", "w");
        if ($fp === false) {
            echo "FATAL ERROR: failed to open file where to save push infos (3)\n";
            exit ;
        }

        for ($i = 0 ; $i < count($push_infos_array) ; $i++) {
            if ($i != $id) {
                if ( fwrite($fp, $push_infos_array[$i] . "\n") === false ) {
                    echo "FATAL ERROR: failed to write file where to save push infos(8)\n";
                    fclose($fp);
                    exit ;
                }
            }
        }
        
        //$delquery = "DELETE FROM nanodb WHERE id = '$id'";
        //$dblog = mysqli_query($db, $delquery);
        //$db->close();
    }
    //$db->close();
    header('Location:main.php');
    exit;
}
?>

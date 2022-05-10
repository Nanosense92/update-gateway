<?php


function delete_remote_db_and_set_new_ones($file)
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once "/home/pi/enocean-gateway/get_database_password.php";

    /* Get file content in a string */
    $file_str = file_get_contents($file);
    if ($file_str === FALSE) {
        echo "DELETE REMOTE DB FATAL ERROR : file_get_contents() failed with file '$file' ... exiting\n";
        return FALSE ;
    }

    /* Get the file content (string) into a json object (assoc array) */
    $json = json_decode($file_str, $assoc = TRUE);
    if ( $json === NULL ) {
        echo "DELETE REMOTE DB FATAL ERROR : json_decode() returned NULL\n";
        return FALSE ;
    }
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        echo "DELETE REMOTE DB FATAL ERROR : json_last_error() shows an error for json_decode()\n";
        return FALSE ;
   	}

    /* Connect to database mysql */
    $db = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');
    if ($db === false || $db->connect_errno) {
        echo "DELETE REMOTE DB FATAL ERROR : mysqli_connect() failed\n"; 
        return FALSE ;
    }

    /* Query to delete everything in the mysql table that holds the remote databases */
    mysqli_query($db, 'DELETE FROM nanodb'); // not checked because the table might not exist or already be empty

    /* Create the nanodb table if it doesn't exist */
    $create_table = 'CREATE TABLE IF NOT EXISTS
        nanodb (ID INT AUTO_INCREMENT, login varchar(255), password varchar(255), addr varchar(255) NOT NULL, port varchar(255) NOT NULL, path varchar(255) NOT NULL, location varchar(255), PRIMARY KEY (ID))';
    if ( mysqli_query($db, $create_table) === FALSE ) {
        echo "DELETE REMOTE DB FATAL ERROR : mysqli_query() failed to create nanodb table\n";
        $db->close();
        return FALSE ;
    }

    /* Insert the new databases from the JSON */
    for ($i = 0 ; $i < $json['number_of_remote_databases'] ; $i++) {
        $login = $json['remote_databases'][$i]['login'];
        $pass = $json['remote_databases'][$i]['password'];
        $addr = $json['remote_databases'][$i]['address'];
        $port = $json['remote_databases'][$i]['port'];
        $path = $json['remote_databases'][$i]['path'];
        $token = $json['remote_databases'][$i]['token'];

        $insert_query = "INSERT INTO nanodb (login, password, addr, port, path, location) VALUES('$login', '$pass', '$addr', '$port', '$path', '$token')";
        if ( mysqli_query($db, $insert_query) === FALSE ) {
            echo "DELETE REMOTE DB FATAL ERROR : mysqli_query() failed to insert into nanodb table\n";
            $db->close();
            return FALSE ;
        }
    } // end for()
    

    /* now it's done, let's disconnect from mysql */
    $db->close();

    return TRUE;
} // end function delete_remote_db_and_set_new_ones()




?>

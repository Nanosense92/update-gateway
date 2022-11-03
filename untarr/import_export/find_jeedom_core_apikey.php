<?php

function find_jeedom_core_apikey()
{

    require "/home/pi/enocean-gateway/get_database_password.php";

    /* FIND IP ADDRESS using ifconfig and parsing ; note : better use ip than ifconfig (deprecated) ! */
    $exec_output = array();
    $exec_ret = 0;
    exec("sudo /sbin/ifconfig | awk '/eth0/,/^$/' | grep -a 'inet ' | cut -d ' ' -f 10", $exec_output, $exec_ret);
    if ($exec_ret !== 0) {
        echo("CREATE ENOCEAN EQUIPMENTS FATAL ERROR: FAILED TO GET CURRENT PRIVATE IP ADDRESS\n");
        return FALSE;
    }
    $jeedom_ip = $exec_output[0];

    /* Find Jeedom API key */
    $db = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');
    if ($db->connect_errno){
        echo("CREATE ENOCEAN EQUIPMENTS FATAL ERROR: FAILED TO CONNECT TO JEEDOM DATABASE\n");
        return FALSE;
    }
    $ret_query = $db->query("SELECT `value` FROM `config` WHERE `plugin`='core' AND `key`='api'");
    $jeedom_api_key = ($ret_query->fetch_array(MYSQLI_BOTH))[0];
    $db->close();

    //echo $jeedom_api_key . "\n";

    $password = file_get_contents("/var/www/html/data/jeedom_encryption.key");
    if ($password === false) {
    	echo "FATAL ERROR: FAILED TO OPEN jeedom_encryption.key\n";
	exit(1);
    }

    $ciphertext = $jeedom_api_key;
    $ciphertext = base64_decode(str_replace('crypt:','',$ciphertext));
    if (!hash_equals(hash_hmac('sha256', substr($ciphertext, 48).substr($ciphertext, 0, 16), hash('sha256', $password, true), true), substr($ciphertext, 16, 32))) {
    	return null;
    }
    $res = openssl_decrypt(substr($ciphertext, 48), "AES-256-CBC", hash('sha256', $password, true), OPENSSL_RAW_DATA, substr($ciphertext, 0, 16));

    //echo "\nres = $res\n";
    return $res;
}
?>

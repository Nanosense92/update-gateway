<?php
require_once "./class_jeedom.php";

/* IP ADDRESS */
$exec_output = array();
$exec_ret = 0;
exec("sudo /sbin/ifconfig | awk '/eth0/,/^$/' | grep -a 'inet ' | cut -d ' ' -f 10", $exec_output, $exec_ret);
if ($exec_ret !== 0) {
    die("FATAL ERROR: FAILED TO GET CURRENT PRIVATE IP ADDRESS\n");
}
$jeedom_ip = $exec_output[0];


/* API KEY */
$db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');
if ($db->connect_errno){
    die("FATAL ERROR: FAILED TO CONNECT TO JEEDOM DATABASE\n");
}

$ret_query = $db->query("SELECT `value` FROM `config` WHERE `plugin`='core' AND `key`='api'");
$jeedom_api_key = ($ret_query->fetch_array(MYSQLI_BOTH))[0];

mysqli_close($db);


/* Jeedom update */
$jsonrpc = new jsonrpcClient($jeedom_ip . '/core/api/jeeApi.php', $jeedom_api_key);
if ( $jsonrpc->sendRequest('update::update', array()) ) {
    print_r($jsonrpc->getResult());
}
else {
    echo $jsonrpc->getError();
}






?>
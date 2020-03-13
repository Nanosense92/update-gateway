<?php

function print_web($obj) {

    echo "<br> <span style=color:blue;> ";
    echo "<pre>";
    echo "val display -> | $obj | type " . gettype($obj);
    if (gettype($obj) == "array") {
        echo "<br>";
        var_dump($obj);
        echo "<br>";
    }
    echo "</pre>";
    echo "<br>  </span>";
}

function display_python_output($output) {
    foreach ($output as $o) {
        //echo "<br>" . $o . "<br>";
        echo $o . "<br>";
    }
}


function load_db() {
    $dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
    if ($dbconnect->connect_errno) {
        printf("Connection to 'jeedom' database failed");
        exit;
    }
    return $dbconnect;
}

function close_db($dbconnect) {
    mysqli_close($dbconnect);  	
}

function check_idslave_db($slave_id) {

    $dbconnect = load_db();
    $ret = "notfound";

    $idq = $dbconnect->query("SELECT logicalId FROM eqLogic WHERE logicalId=$slave_id");
    $id = $idq->fetch_array(MYSQLI_BOTH);

    if (empty($id['logicalId']) === false) {
        $ret = "found";
    } 
    close_db($dbconnect);
    return $ret;
}

function add_device_to_eqLogic($dev) {

    $dbconnect = load_db();

    $sl = $dev['slave_id'];
    $reg = $dev['registers'];
    $ty = $dev['type'];
    $na = $dev['name'];

    print_web($reg);
    $res = $dbconnect->query("INSERT INTO eqLogic (logicalId,`status`, eqType_name,`name`, generic_type, isVisible, isEnable) VALUES ('$sl',\"$reg\",'modbus','$na','$ty',1,1)");
    echo "this->" . $dbconnect->error;
    close_db($dbconnect);
}

function delete_device_from_eqLogic($slave_id) {
    $dbconnect = load_db();
    $dbconnect->query("DELETE FROM eqLogic WHERE logicalId=$slave_id");	
    echo $dbconnect->error;
    close_db($dbconnect);
}

function in_hist() {

    
    //detecte sondes et mets leurs valeurs dans data.ini
    exec("sudo /usr/bin/python3.5 modbus_py/scan.py 2>&1", $output, $return_value);
    display_python_output($output);
    echo $output;
    $xfile = "modbus_py/modbus__cache/data.ini"; 
    $modbus_cache = parse_ini_file($xfile, true);
    print_web($modbus_cache);

    





}

?>
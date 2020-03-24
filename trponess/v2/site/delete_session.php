<?php

$name = $_GET['name'];
if ($name === "")
    echo "can't delete a new device";
else {

    echo "===" . $name;

    function delete_session($name) {

        exec("sudo python3 ../modbus_py/session.py delete name=$name 2>&1", $output, $return_value);
        $ret_parse_ini = parse_ini_file("../modbus_py/modbus__cache/session.ini", true);
        if ($ret_parse_ini === false) {echo "parse_ini_file(session) failed"; exit(2);}
    }

    delete_session($name);
    echo "<script> window,alert(\"DELETED $name going back to main page\"); </script>";
	echo "<script> document.location.href='main2.php'; </script>";
}

?>
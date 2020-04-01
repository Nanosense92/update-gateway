<?php 
    $in = $_POST['scan'];

    echo "<script> window.open('main2.php', '_blank');    </script>";
    exec("sudo python3 ../modbus_py/scan_find_devices.py $in 2>&1", $output, $return_value);

    foreach ($output as $k => $v) {
        echo $v . "<br>";
    }

    
    //echo "<script> window.alert(\"SAVED going back to main page\"); </script>";
    //echo "<script> document.location.href='main2.php'; </script>";

    


    
    
?>
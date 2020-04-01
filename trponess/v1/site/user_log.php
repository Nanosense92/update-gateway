<div class=top_left_corner><button class=button_back_to_main type=button onclick="document.location.href='main2.php'" >Retourner à la page principale</button></div>

<?php


    exec("sudo python3 ../modbus_py/display_user_log.py 2>&1", $output, $return_value);
    foreach ($output as $k => $v) {
        echo $v . "<br>";
    }

?>
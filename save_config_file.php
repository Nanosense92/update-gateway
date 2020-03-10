
<?php
   
if ( isset($_GET['filename_chosen']) ) {
    
    $filename_chosen = $_GET['filename_chosen'];
    exec("sudo cp /home/pi/modbus-gateway/modbus__cache/cache_modbus.ini  /home/pi/modbus-gateway/user__cache/$filename_chosen  2>&1", $output, $return_value);

    echo " <script> window.close() </script> ";
}









?>

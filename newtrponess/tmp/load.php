<!DOCTYPE HTML>  
<html>
<head>
    <script src="jkwery.js"></script>
        
    

</head>
<body style="background-color:white;">


<script>
    function spawn_buttonS(modbus_cache) 
    {   
        for (device_name in modbus_cache) {
            //document.write(device_name);
            spawn_button(device_name);
        }
    }

    function spawn_button(name) 
    {
        var button = document.createElement("button");
        button.innerHTML = name;

        // 2. Append somewhere
        var body = document.getElementsByTagName("body")[0];
        body.appendChild(button);

        // 3. Add event handler
        button.addEventListener ("click", function() {
            // window.location.href = 'modbus/formulaire.php?device_chosen=' + name;
            window.open(
            'formulaire.php?device_chosen=' + name,
            '_blank' // <- This is what makes it open in a new window.
            );

            //sessionStorage.setItem("device_chosen", String(name));
        });
    }
</script>

<?php 

$file = $_GET['file'];


$xfile = "/home/pi/modbus-gateway/user__cache/$file"; 
$modbus_cache = parse_ini_file($xfile, true);
$kk = json_encode($modbus_cache);

echo "<script>spawn_buttonS($kk);</script>";

exec("sudo rm -f /home/pi/modbus-gateway/modbus__cache/cache_modbus.ini  2>&1", $output, $return_value);
exec("sudo cp /home/pi/modbus-gateway/user__cache/$file /home/pi/modbus-gateway/modbus__cache/cache_modbus.ini  2>&1", $output, $return_value);
var_dump($output);


?>



</body>
</html>




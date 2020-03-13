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
           // document.write(device_name);
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

    function save_config()
    {
        var input_val = document.getElementById('id_input').value;
        //input_val = document.getElementsByName('name_button').value;

        if (input_val !== "") {
            //console.log("INPUT VAL = ");
            //console.log(input_val);

            window.open(
            'save_config_file.php?filename_chosen=' + input_val,
            '_blank' // <- This is what makes it open in a new window.
            );

        }
    }


</script>



<?php 
    //echo "<script type='text/javascript'>alert('Scan des appareils connectés en cours Veuillez patienter svp');</script>";
    exec("sudo /usr/bin/python3.5 /home/pi/modbus-gateway/scan.py scan 2 2>&1", $output, $return_value);

    if ($return_value == 42) {
        echo nl2br("Aucun appareil n'a été détecté\nAbandon de la configuration Modbus\nVeuillez vérifier les branchements et alimentations des appareils");
        exit(1);
    }
    



    $xfile = "/home/pi/modbus-gateway/modbus__cache/cache_modbus.ini"; 
    $modbus_cache = parse_ini_file($xfile, true);
    
    $kk = json_encode($modbus_cache);

    echo "<script>spawn_buttonS($kk);</script>";

    echo "<br><br> Vous pouvez sauvegarder la configuration scannée, afin de la réutiliser dans le futur, plutôt que de devoir re-scanner cette même configuration<br>";
    echo "Choisissez un nom pour la configuration courante à sauvegarder > ";

    echo "<input type=\"text\" id=\"id_input\" name=\"name_input\" required minlength=\"1\" maxlength=\"42\" size=\"42\">";

    echo "<button name=\"name_button\" onclick=\"save_config()\">Sauvegarder la configuration scannée</button> ";

?>



</body>
</html>




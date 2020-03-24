
<!DOCTYPE HTML>  
<html>
<head>

</head>
<body style="background-color:#333333;">  

<link rel="stylesheet" href="css/main.css">


<script>
    function alert_and_launch_scan(nb_scans)
    {
        setTimeout(function() { alert('Scan des appareils connectés en cours\nVeuillez patienter svp'); }, 1);
        var site = "scan.php?nb_scans=" + nb_scans;
        window.location.replace(site);
    }
</script>


<?php
    $nb_scansErr = "";
    $nb_scans = 0;

    function test_input_nb_scans($nb_scan_to_check)
    {   
        if ($nb_scan_to_check === "") {
            return true;
        }

        $explode_ret = array();
        $explode_ret = explode(',', $nb_scan_to_check);
         
        foreach ($explode_ret as $elem) {
            if ( is_numeric($elem) === false ) {
                return false;
            }
            
            $len = strlen($elem);
            for ( $i = 0 ; $i < $len ; $i++ ) {
                
                if ( ctype_space($elem[$i]) === true || $elem[$i] === '-' ) {
                    return false;
                }
            }
        }

        return true;
    }

    

    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ( isset($_POST['nb_scans']) ) {
            $x = test_input_nb_scans($_POST['nb_scans']);
            if ($x === false) {
                //echo "ERROR";
                $nb_scansErr = "id_sonde OU id_sonde,id_sonde,.....";

                echo "<form id=\"idform_nb_scans\" method=\"post\" action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\">
                        <span class=simple_text> Entrez les identifiants des sondes à détecter (format 1,2,3) Laissez le champ vide pour scanner tous les appareils possibles (dure plusieurs minutes) : </span>
                        <input class=input_field type=\"text\" name=\"nb_scans\" value=\"\">
                        <span class=error_text>*" . $nb_scansErr . "</span>
                        <input class=button_valider type=\"submit\" name=\"submit\" value=\"Valider\">
                    </form>
                    ";

            }
            else {
                $nb_scans = $_POST['nb_scans'];
                if ($nb_scans == "") {
                    $x = "1 - 254";
                }
            
                echo "<span style=\"color:red\"> En cliquant sur 'Scan', vous cherchez les sondes $nb_scans";
                echo "</span><br>";
                
                /* SCAN BUTTON */
                
                echo "<span style=color:#DCDCDC font-weight: bold;> Pour scanner les appareils Modbus connectés à la passerelle > </span>";
                echo "<button class=button_scan onclick=alert_and_launch_scan(\"$nb_scans\")>Scan</button> ";
                echo "<span style=color:#DCDCDC font-weight: bold;> < (le scan peut durer quelques minutes)</span>";


               


            
            } /* else */


        } /*  if ( isset($_POST['nb_scans']) ) */

    }  /* if ($_SERVER["REQUEST_METHOD"] == "POST") */
    else {
        echo "<form id=\"idform_nb_scans\" method=\"post\" action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\">
                <span class=simple_text> Entrez les identifiants des sondes à détecter (format 1,2,3) Laissez le champ vide pour scanner tous les appareils possibles (dure plusieurs minutes) : </span>
                <input class=input_field type=\"text\" name=\"nb_scans\" value=\"\">
                <span class=error_text>*" . $nb_scansErr . "</span>
                <input class=button_valider type=\"submit\" name=\"submit\" value=\"Valider\">
            </form>
            ";

    } /* else */

    echo "<br><br> <span style=color:white;> Appareils connus </span> <br>";    

?>



<?php

//----------------------------------------------------------------------------------------------------------------------------------------------------------

    function print_web($obj) 
    {
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

    /*
    print db_result() {
        $dbconnect  = load_db();
        $x = $dbconnect->query("SELECT id FROM eqLogic");
        $x = $dbconnect->query("SELECT `name` FROM eqLogic");
        $x = $dbconnect->query("SELECT `name` FROM eqLogic");

        

        close_db($dbconnect);
    }*/

    function load_db() 
    {
        $dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
        if ($dbconnect->connect_errno) {
            printf("Connection to 'jeedom' database failed");
            exit(55);
        }
        return $dbconnect;
    }

    function close_db($dbconnect)
    {
        mysqli_close($dbconnect);  	
    }

    function fetch_name_logicid_eqLogic() 
    {
        $dbconnect  = load_db();
        $x = $dbconnect->query("SELECT `name` FROM eqLogic");

        $tab = array();
        while ( $tmp = $x->fetch_assoc() ) {
            array_push($tab, $tmp['name']);
        }
        close_db($dbconnect);
        return $tab;
    }

    function spawn_buttons() 
    {
        $data = fetch_name_logicid_eqLogic();
        foreach($data as $d) {
            echo "<script>
                    spawn_button(\"$d\");
                </script>";
        }
    }


?>


<script>
    function spawn_button(name) 
    {
        var button = document.createElement("button");
        button.innerHTML = name;
        button.id = "zbuttons";

        var body = document.getElementsByTagName("body")[0];
        body.appendChild(button);

        button.addEventListener ("click", 
            function() {
                /*window.open('formulaire.php?device_chosen=' + name, '_blank'); */
                document.location.href = 'formulaire.php?device_chosen=' + name;
            }
        );


    }
</script>

<?php //END FUNCTIONS-----------------------------------------------------------------------------------------------------------------------------------------------------------?>


<?php

    spawn_buttons();

    /* SEE LAST MEASURED VALUES */
    echo "<br><br><br> <button class=button_see_values onclick=\"window.open('display_values_real_time.php','_blank');\">
            Voir les dernières valeurs mesurées
        </button>";

    /* BACK TO NANOSENSE WEB GUI */
    echo "<br><br><br> <button class=button_see_values style=background:grey; onclick=\"document.location.href='/nanosense/main.php';\">
            Revenir à l'interface web Nanosense
        </button>";



    /*
    function in_hist() {
        $dbconnect  = load_db();
        $xfile = "modbus_py/modbus__cache/data.ini"; 
        $data_cache = parse_ini_file($xfile, true);
        foreach ($data_cache as $data) {
            $name = 
            $date = $i;
            $
            $dbconnect->query("INSERT INTO history (cmd_id, `name`,`datetime`) VALUES ('$data['']', '$tmp[1]', '$tmp[2]')");
        }
        
        foreach($) {

        }


        close_db($dbconnect);
    }*/

    /*
    function get_cmd_id_from_eqlogic_alias($alias) {

        $dbconnect  = load_db();

        $x = $dbconnect->query("SELECT id,`name` FROM cmd WHERE eqType=\"$alias\"");
        $aliases = array();

        while ($tmp = $x->fetch_assoc()) {
            array_push($aliases, $tmp['id']);
        }
        

        close_db($dbconnect);
        return $aliases;
    }


    function in_hist() {
        
        $dbconnect  = load_db();
        //detecte sondes et mets leurs valeurs dans data.ini
        //exec("sudo /usr/bin/python3.5 modbus_py/scan.py 7 2>&1", $output, $return_value);
        //display_python_output($output);
        //echo $output;
        $xfile = "modbus_py/modbus__cache/data.ini"; 
        $modbus_cache = parse_ini_file($xfile, true);
        //print_web($modbus_cache);

        $x = $dbconnect->query("SELECT `name` FROM eqLogic");
        $aliases = array();

        while ($tmp = $x->fetch_assoc()) {
            array_push($aliases, $tmp['name']);
        }
        print_web($aliases);


        foreach ($aliases as $a) {
            $cmd_ids = get_cmd_id_from_eqlogic_alias($a);
            //foreach ($cmd_ids as $a) {

            
            echo $cmd_ids[0]['id'] . "/" . $cmd_ids[0]['name'] . "</br>";

        
            
        }
        /*
        foreach ($modbus_cache as $key => $value) {
            print_web($key);
        }



        close_db($dbconnect);
    }
    */

    echo "<button class=button_scan onclick=alert_and_launch_scan(\"$nb_scans\")>add device</button> ";
    echo "<button class=button_scan onclick=alert_and_launch_scan(\"$nb_scans\")>log</button> ";
    echo "<button class=button_scan onclick=alert_and_launch_scan(\"$nb_scans\")>crontab</button> ";
    
?>





</body>
</html>






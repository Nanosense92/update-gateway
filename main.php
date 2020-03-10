
<!DOCTYPE HTML>  
<html>
<head>
    <script src="jkwery.js"></script>
        
    <style>
        .ask_nb_scans {color: red; font-weight: bold;}
        .error {color: #FF0000;}
    </style>

</head>
<body style="background-color:white;"> 
<!-- 212121 -->




<script>
    function alert_and_launch_scan(nb_scans)
    {
        setTimeout(function() { alert('Scan des appareils connectés en cours\nVeuillez patienter svp'); }, 1);
        console.log(nb_scans);
        var site = "scan.php?nb_scans=" + nb_scans;

        window.location.replace(site);
        //window.location.replace('lol.php');
    }


    function chepatest()
    {
        console.log("UNLUCKY");
        var input_val = document.getElementById('pet-select').value;
        //window.location.replace('load.php?file=' + input_val);

    }


</script>


<?php
    $nb_scansErr = "";
    $nb_scans = 0;



    
    // RETURN THE NUMBER ENTERED BY THE USER, OR FALSE IF THE USER INPUT IS NOT A POSITIVE NUMBER
    function test_input_nb_scans($nb_scan_to_check)
    {
        /*$p = str_split($nb_scan_to_check , ',')
        foreach ($p as $x) {

        }
         
        if ( preg_match("#^[1-9][0-9]{0,3}$#", $nb_scan_to_check) === 1 ) {
            return (int)$nb_scan_to_check;
        }*/
        return (int)$nb_scan_to_check;
        //return FALSE;
    }

    

    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ( isset($_POST['nb_scans']) ) {
            //$nb_scans = test_input_nb_scans($_POST['nb_scans']);
            $nb_scans = $_POST['nb_scans'];//ex EMPTY or nothing

            if ($nb_scans === FALSE) {
                //echo "ERROR";
                $nb_scansErr = "Veuillez entrer un nombre positif (le nombre d'appareils modbus que vous souhaitez configurer)";

                echo "<span class=\"ask_nb_scans\">
                        <form id=\"idform_nb_scans\" method=\"post\" action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\">
                            Entrez le nombre de sondes à détecter :
                            <input type=\"text\" name=\"nb_scans\" value=\"1\">
                            <span class=\"error\">*" . $nb_scansErr . "</span>
                            <input type=\"submit\" name=\"submit\" value=\"Valider\">
                        </form>
                    </span>";

            }
            else {
                
                if ($nb_scans == "") {
                    $x = "tout";
                }
                else {
                    $x = $nb_scans;
                }
                echo "<span style=\"color:red\"> En cliquant sur 'Scan', vous cherchez $x";
                echo ($nb_scans > 1) ? ("s") : ("") ; 
                echo "</span><br>";
                
                // SCAN BUTTON
                echo "THISHITHIHSIHSIHISYHISH" . $nb_scans;
                echo "<span style=color:#DCDCDC font-weight: bold;> Pour scanner les appareils Modbus connectés à la passerelle > </span>";
                echo "<button onclick=alert_and_launch_scan(\"$nb_scans\")>Scan</button> ";
                echo "<span style=color:#DCDCDC font-weight: bold;> < (le scan peut durer quelques minutes)</span>";


                // LOAD FILE BUTTON
                echo "<div id=id_div><br><br><span style=color:#DCDCDC font-weight: bold;>Pour utiliser le fichier de configuration sauvegardé sur la passerelle > </span>";
                //<div id=identifiant_de_ma_div>Votre contenu est placé ici</div>
              //  echo "<button onclick=\"display_scrolling_menu()\">Charger une configuration sauvegardée</button>";
                

                // FIND ALL THE SAVED FILES
                $scandir_ret = scandir("/home/pi/modbus-gateway/user__cache");
                //var_dump($scandir_ret);

            //    echo "AVANT";
                echo "<select name=\"choosing_ini\" id=\"pet-select\">";
                foreach ($scandir_ret as $key => $value) {
                    if ($value[0] != '.') {
                        echo "<option value=\"$value\">$value</option>";
                    }
                }
                echo "</select>";
                
                echo "<button onclick=\"chepatest()\">Charger</button> ";
            
              
              
                echo "<span style=color:#DCDCDC font-weight: bold;> < (opération rapide)</span></div>";


            } // else 


        } //  if ( isset($_POST['nb_scans']) )

        // else if ( isset($_POST['username']) ) {
        //     echo "USERNAME";
        // }

        



    }  // if ($_SERVER["REQUEST_METHOD"] == "POST")
    else {
        // FORM THAT ASKS FOR THE NUMBER OF DEVICES TO SCAN
        echo "<span class=\"ask_nb_scans\">
        <form id=\"idform_nb_scans\" method=\"post\" action=\"" . htmlspecialchars($_SERVER["PHP_SELF"]) . "\">
            Entrez le nombre de sondes à détecter :
            <input type=\"text\" name=\"nb_scans\" value=\"1\">
            <span class=\"error\">*" . $nb_scansErr . "</span>
            <input type=\"submit\" name=\"submit\" value=\"Valider\">
        </form>
    </span>";



    } // else

    

?>

"existing devices in db"

<?php

//----------------------------------------------------------------------------------------------------------------------------------------------------------
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

function fetch_name_logicid_eqLogic() {

    $dbconnect  = load_db();
    $x = $dbconnect->query("SELECT `name`,logicalId FROM eqLogic");
    close_db($dbconnect);
    return $x;
}

//-----------------------------------------------------------------------------------------------------------------------------------------------------------




?>

<script>
    function spawn_buttonS(modbus_cache) 
    {    
        for (key in modbus_cache) {
            var v = modbus_cache[key];
            spawn_button(key + v);
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
   
//LOAD SESSION
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//load form db all the info and fill the fields

/*
function load_db() {
    $dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
    if ($dbconnect->connect_errno) {
        printf("Connection to 'jeedom' database failed");
        exit;
    }
    return $dbconnect;
}

function fetch_eqLogic($dbconnect) {
	return $dbconnect->query("SELECT `name`,logicalId FROM eqLogic");
}



$dbconnect = load_db();

foreach (fetch_eqLogic($dbconnect) as $key => $value){
    
    $logid = $value['logicalId'];
    $name = $value['name'];
    echo "<script>spawn_button(\"$name\");</script>";

}
*/






/*
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
*/

?>



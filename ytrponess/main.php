
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
                
                echo "<span style=color:#DCDCDC font-weight: bold;> Pour scanner les appareils Modbus connectés à la passerelle > </span>";
                echo "<button onclick=alert_and_launch_scan(\"$nb_scans\")>Scan</button> ";
                echo "<span style=color:#DCDCDC font-weight: bold;> < (le scan peut durer quelques minutes)</span>";


            
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

    echo "<br>existing devices in db<br>";    

?>



<?php

//----------------------------------------------------------------------------------------------------------------------------------------------------------

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

/*
print db_result() {
    $dbconnect  = load_db();
    $x = $dbconnect->query("SELECT id FROM eqLogic");
    $x = $dbconnect->query("SELECT `name` FROM eqLogic");
    $x = $dbconnect->query("SELECT `name` FROM eqLogic");

    

    close_db($dbconnect);
}*/

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
    $x = $dbconnect->query("SELECT `name` FROM eqLogic");

    $tab = array();
    while ($tmp = $x->fetch_assoc()) {
        array_push($tab, $tmp['name']);
    }
    close_db($dbconnect);
    return $tab;
}

function spawn_buttons() {

    $data = fetch_name_logicid_eqLogic();
    foreach($data as $d) {
    echo "<script>spawn_button(\"$d\");</script>";
    }
}


?>

<script>
function spawn_button(name) 
{
    var button = document.createElement("button");
    button.innerHTML = name;

    
    var body = document.getElementsByTagName("body")[0];
    body.appendChild(button);

    button.addEventListener ("click", function() {
        
        window.open(
        'formulaire.php?device_chosen=' + name,
        '_blank' 
        );        
    });
}
</script>

<?php //END FUNCTIONS-----------------------------------------------------------------------------------------------------------------------------------------------------------?>


<?php
spawn_buttons();
?>





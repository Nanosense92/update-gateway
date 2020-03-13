
<!DOCTYPE HTML>  
<html>
<head>
<style>
	.error {color: #FF0000;}
	.field_to_fill {color: #DCDCDC; font-weight: bold;}
	h2 {color: grey; text-decoration: underline overline;}
</style>
</head>
<body style="background-color:#212121;">

<span class="field_to_fill">
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$device_chosen = "";
$tmp_file = "";


/*
THIS SAVES THE VAL DEVICE CHOSEN AFTER FORM SUBMIT
*/
if (empty($_GET['device_chosen']) == FALSE) {
	echo "PREMIERE FOIS    ";
	$device_chosen = $_GET['device_chosen'];//dont need it get rid of it
	$alias = $_GET['device_chosen'];


	$tmp_file = fopen("tmp_file", "w+");
	if ($tmp_file === false)
		exit(123);
	fwrite($tmp_file, $device_chosen); 
	fclose($tmp_file);
}
else {
	echo("SECONDE FOIS    ");
	$tmp_file = fopen("tmp_file", "r");
	if ($tmp_file === false)
		exit(123);
	$device_chosen = file_get_contents("tmp_file");
	$alias = file_get_contents("tmp_file");
	fclose($tmp_file);
}



// define variables and set to empty values
$aliasErr = $parentobjErr = $visibleErr = $activerErr = "";
$parentobj_id;



//load from db

function fetch_eqLogic_form($dbconnect) {
	$res_query= $dbconnect->query("SELECT isVisible,isEnable FROM eqLogic");
	$res = $res_query->fetch_array(MYSQLI_BOTH);
	return $res;
}

$dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
$db_data = fetch_eqLogic_form($dbconnect);

$isVisible = $db_data['isVisible'];
$isEnable = $db_data['isEnable'];



$visible = $isVisible;
$activer = $isEnable;








function test_input($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	if (empty($_POST["alias"])) {
		$aliasErr = "Alias is required";
  } 
  	else {
		$tmpalias = test_input($_POST["alias"]);
		// check if name only contains letters and whitespace
		if (preg_match("/^[0-9a-zA-Z_-]*$/", $tmpalias) == 0) {
	  		$aliasErr = "Only letters and white space allowed";
		}
		$alias = $tmpalias;
  	}


	if (empty($_POST["activer"])) {
		$activerErr = "Activer is required";
  	} 
  	else {
		$activer = test_input($_POST["activer"]);
  	}


  	if (empty($_POST["visible"])) {
		$visibleErr = "Visible is required";
  	} 
  	else {
		$visible = test_input($_POST["visible"]);
	  }
	  
	$parentobj_id = test_input($_POST["select_parentobj"]);


	
	echo "<br><br><h3>DEBUG:</h3>";
	echo "alias = $alias<br>";
	echo "activer = $activer<br>";
	echo "visible = $visible<br>";
	echo "parentobj = $parentobj_id<br>";

	$dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
	if ($dbconnect->connect_errno) {
		printf("Connection to 'jeedom' database failed");
		exit;
	}
	//IN DB-------------------------------------------------------------

	//main loads from db , the only option you got is to update


	/*function is_alias_db($dbconnect, $alias) {

		$ids_obj = $dbconnect->query("SELECT `name`,id FROM eqLogic");
		while ($tmp = $ids_obj->fetch_assoc()) {
			echo $tmp['name'] . "<br>"; 
			echo $alias;
			if ($tmp['name'] == $alias) {
				echo "PRESENT";
				return true;
			}
		}
		return false;
	}*/


	function form_to_db($dbconnect, $device_chosen,  $alias, $isVisible, $isEnable) {

		$tmp = array();

		//$isVisible = ($visible === "Oui") ? (1) : (0);
		//$isEnable = ($activer === "Oui") ? (1) : (0);
		array_push($tmp, $alias);//name
		array_push($tmp, $isVisible);
		array_push($tmp, $isEnable);
	
		//$dbconnect->query("INSERT INTO eqLogic (`name`,isVisible, isEnable) VALUES ('$tmp[0]', '$tmp[1]', '$tmp[2]')");
		$dbconnect->query("UPDATE eqLogic SET name=$alias WHERE name=$device_chosen");
		
	}


	/*$b = (int)is_alias_db($dbconnect, $alias);
	echo "--------------------------" . $b;
	if ($b == 1) {
		//take all values change
		echo "here";
		form_to_db($dbconnect, $device_chosen, $alias, $visible, $activer);
	} else {
		echo "here2";
		exit(0);
	}*/

	exit(0);
	
	//NOT IN DB-------------------------------------------------------------
	$xfile = "/home/pi/modbus-gateway/modbus__cache/cache_modbus.ini"; 
	$modbus_cache = parse_ini_file($xfile, true);
	var_dump($modbus_cache);
	#$kk = json_encode($modbus_cache);
	#$kkc = JSON.parse($kk);
	
	/*
	echo "DEVICE_CHOSEN = $device_chosen   " ;
	$dbconnect->query("UPDATE eqLogic SET name = '$alias' WHERE name = '$device_chosen'");
	*/
	// SET VISIBLE
	//$isVisible = ($visible === "Oui") ? (1) : (0);
	//$isEnable = ($activer === "Oui") ? (1) : (0);
	
	$tmp = array();
	array_push($tmp, $modbus_cache[$device_chosen]['slave_id']);//logicalId
	array_push($tmp, $modbus_cache[$device_chosen]['mode']);//status
	array_push($tmp, "modbus");//eqType_name
	array_push($tmp, $alias);//name
	array_push($tmp, $isVisible);
	array_push($tmp, $isEnable);

	/*
	function generate_unik_id($dbconnect, $table) {
		
		$ids = array();
		$ids_obj = $dbconnect->query("SELECT id FROM $table");
		while ($tmp = $ids_obj->fetch_assoc()) {
			array_push($ids, $tmp['id']);
		}

        echo "<pre>" , var_dump($ids) , "</pre>";
        do { 
            $randomNumber = rand(13, 15);//(0,2147483646);
        } while (in_array($randomNumber, $ids));
    
		return $randomNumber;
		array_push($tmp, generate_unik_id($dbconnect, "eqLogic"));
    }*/
	
	

	

	var_dump($tmp);

	echo "llllll" . $device_chosen;

	$dbconnect->query("INSERT INTO eqLogic (logicalId,`status`,eqType_name,`name`,isVisible, isEnable, id) VALUES ('$tmp[0]', '$tmp[1]', '$tmp[2]', '$tmp[3]', $tmp[4], $tmp[5], $tmp[6])");
	$res_query = $dbconnect->query("SELECT id FROM eqLogic WHERE (`name`='$alias')");
	if (is_bool($res_query)) {
		echo "<br>";
		echo "faile d to push";
		exit(44);
	}
	$res = $res_query->fetch_array(MYSQLI_BOTH);
	
	echo "<br>";
	echo "<br>";
	var_dump($res);
	echo("---------------------------->" . $res['id']);
	echo "<br>";
	echo "<br>";
	$eqLogic_unik_id = $res['id']; 

	$tmp = array();

	if ($modbus_cache[$device_chosen]['type'] == 'e4000') {

		array_push($tmp, 'Temperature');//name
		array_push($tmp, 'modbus');//eqType
		array_push($tmp, "TMP::value");//logicalId
		array_push($tmp, $eqLogic_unik_id);//eqLogic_id

		array_push($tmp, 'Humidity');//name
		array_push($tmp, 'modbus');//eqType
		array_push($tmp, "HUM::value");//logicalId
		array_push($tmp, $eqLogic_unik_id);//eqLogic_id


		array_push($tmp, 'CO2');//name
		array_push($tmp, 'modbus');//eqType
		array_push($tmp, "CONC::value");//logicalId
		array_push($tmp, $eqLogic_unik_id);//eqLogic_id

		array_push($tmp, 'Total');//name
		array_push($tmp, 'modbus');//eqType
		array_push($tmp, "total");//logicalId
		array_push($tmp, $eqLogic_unik_id);//eqLogic_id

	} else {

		array_push($tmp, 'PM10');//name
		array_push($tmp, 'modbus');//eqType
		array_push($tmp, "PM10::value");//logicalId
		array_push($tmp, $eqLogic_unik_id);//eqLogic_id

		array_push($tmp, 'PM2.5');//name
		array_push($tmp, 'modbus');//eqType
		array_push($tmp, "PM2.5::value");//logicalId
		array_push($tmp, $eqLogic_unik_id);//eqLogic_id

		array_push($tmp, 'PM1');//name
		array_push($tmp, 'modbus');//eqType
		array_push($tmp, "PM1::value");//logicalId
		array_push($tmp, $eqLogic_unik_id);//eqLogic_id
	}

	echo "<br>co2  ";
	var_dump($tmp);

	$i = 0;
	while ($i < count($tmp)) {
		$a = $tmp[$i];
		$b = $tmp[$i + 1];
		$c = $tmp[$i + 2];
		$d = $tmp[$i + 3];
		$x = $dbconnect->query("INSERT INTO cmd (`name`,eqType,logicalId,eqLogic_id) VALUES ('$a', '$b', '$c', $d)");	
		
		$i += 4;

		echo "<br> <span style=color:green;> ";
		echo count($tmp);
		echo $eqLogic_unik_id . "----";
		
		echo ("$a $b $c $d");
		echo "<br>  </span>";
		
	}


	


	//HISTORY
	//be able to launch freely everytime called
	/*
	$res = $res_query->fetch_array(MYSQLI_BOTH); 
	$cmd_unik_id = $res['id'];
	$currentDate = date('Y-m-d', time()); 
	$datafile = "/home/pi/modbus-gateway/modbus__cache/data.ini"; 
	$data = parse_ini_file($datafile, true);

	
	$res_query = $dbconnect->query("SELECT id FROM eqLogic WHERE (`name`='$alias')");

	
	if (count($tmp) == 4) {

		$x = $dbconnect->query("INSERT INTO history (`datetime`, $cmd_unik_id, `value`) VALUES ('$a', '$b', '$c', $d)");	

		$data[key]['val'] =
	} else {

	}*/

	






	



/*
	$dbconnect->query("INSERT INTO eqLogic (logicalId,`status`,eqType_name,`name`,isVisible, isEnable) VALUES ('$tmp[0]', '$tmp[1]', '$tmp[2]', '$tmp[3]', $tmp[4], $tmp[5])");
	$res_query = $dbconnect->query("SELECT id FROM eqLogic WHERE (`name`='pluto')");
	$res = $res_query->fetch_array(MYSQLI_BOTH);
	echo "<br>";
	var_dump($res);
	$eqLogic_unik_id = $res['id']; 


*/
	

	
	
	



	mysqli_close($dbconnect);  		
}


?>
</span>

<?php 
/*
	function fetch_eqLogic_form($dbconnect) {
		return $dbconnect->query("SELECT `isVisible,isEnable` FROM eqLogic");
	}

	$dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
	$db_data = fetch_eqLogic_form($dbconnect);

	
*/
?>

<h2>Device Configuration : <?php echo $device_chosen; ?></h2>
<p><span class="error">* required field</span></p>

<form id="main_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
	<!-- Alias:  -->
	<span class="field_to_fill">Alias</span>
	<input type="text" name="Alias" value="<?php echo $alias;?>">
	<span class="error">* <?php echo $aliasErr;?></span>
	<br><br>


	<!-- Activer: -->
	<span class="field_to_fill">Activer
		<input type="radio" name="activer" <?php if ($activer==1) echo "checked";?> value="Oui">Oui
		<input type="radio" name="activer" <?php if ($activer==0) echo "checked";?> value="Non">Non
	</span>
	<span class="error">* <?php echo $activerErr;?></span>
	<br><br>

	<!-- Visible: -->
	<span class="field_to_fill">Visible
		<input type="radio" name="visible" <?php if ($visible==1) echo "checked";?> value="Oui">Oui
		<input type="radio" name="visible" <?php if ($visible==0) echo "checked";?> value="Non">Non
	</span>
	<span class="error">* <?php echo $visibleErr;?></span>
	<br><br>
	 
	<!-- <label for="pet-select">Objet parent:</label> -->
	<span class="field_to_fill">Objet parent:</span>
	<select name="select_parentobj" id="id_parentobj">
		<option value="">Aucun</option>
		<?php 
			$dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
			if ($dbconnect->connect_errno) {
				printf("Connection to 'jeedom' database failed");
				exit;
			}

			$ret_query = $dbconnect->query("SELECT id, name FROM object ORDER BY position");
			while ($ret_query_row = $ret_query->fetch_array(MYSQLI_BOTH)) {
				//var_dump($ret_query_row);
				$tmp_objparent_name = $ret_query_row["name"];
				$tmp_objparent_id = $ret_query_row["id"];
				echo "<option value=\"$tmp_objparent_id\">$tmp_objparent_name</option>";
			}
			mysqli_close($dbconnect);  
		?>
	</select>

	<br><br><br><br><br><br><br><br>
	<input type="submit" name="submit" value="Valider"> 
</form>




<!-- // E4000NG  COV  A5 09 0C
// E4000NG  CO2 HUM TEMP  D2 04 08
// P4000  PMs  A5 09 07 -->




</body>
</html>

$dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
		if ($dbconnect->connect_errno) {
			printf("Connection to 'jeedom' database failed");
			exit;
		}
		function check_alias ($dbconnect, $alias) {
			$ids_obj = $dbconnect->query("SELECT `name` FROM eqLogic");
			while ($tmp = $ids_obj->fetch_assoc()) {
				if ($tmp['name'] == $alias) {
					return 0;
				}
			}
			return 1;
		}
		mysqli_close($dbconnect); 









$activerEr

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
    function alert_and_launch_scan()
    {
        setTimeout(function() { alert('Scan des appareils connectés en cours\nVeuillez patienter svp'); }, 1);
        window.location.replace('lol.php');
    }


    function chepatest()
    {
        var input_val = document.getElementById('pet-select').value;
        window.location.replace('load.php?file=' + input_val);

    }


</script>


<?php
    $nb_scansErr = "";
    $nb_scans = 0;



    
    // RETURN THE NUMBER ENTERED BY THE USER, OR FALSE IF THE USER INPUT IS NOT A POSITIVE NUMBER
    function test_input_nb_scans($nb_scan_to_check)
    {
        if ( preg_match("#^[1-9][0-9]{0,3}$#", $nb_scan_to_check) === 1 ) {
            return (int)$nb_scan_to_check;
        }
    
        return FALSE;
    }

    

    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ( isset($_POST['nb_scans']) ) {
            $nb_scans = test_input_nb_scans($_POST['nb_scans']);
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

                echo "<span style=\"color:red\"> En cliquant sur 'Scan', vous preparez la confuguration de $nb_scans appareil";
                echo ($nb_scans > 1) ? ("s") : ("") ; 
                echo "</span><br>";
                
                // SCAN BUTTON
                echo "<span style=color:#DCDCDC font-weight: bold;> Pour scanner les appareils Modbus connectés à la passerelle > </span>";
                echo "<button onclick=\"alert_and_launch_scan()\">Scan</button> ";
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






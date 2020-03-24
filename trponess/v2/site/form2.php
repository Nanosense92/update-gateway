
<!DOCTYPE HTML>  
<html>
<head>
<style>
	.error {
		color: #FF0000;
	}

	.field_to_fill {
		color: #DCDCDC;
		font-weight: bold;
		height: 150;
		padding: 8px;
		border: none;
		border-bottom: 1px solid #ccc;
		/* width: 10%; */
		size: 10;
	} 

	.button_back_to_main {
		color: white;
		border-radius: 4px;
		text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
		background: grey;
		font-size: 100%;
	}

	.button_destroy {
		color: white;
		border-radius: 4px;
		text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
		background: red;
		font-size: 100%;
	}

	h2 {
		color: grey;
		text-decoration: underline overline;
	}
</style>
</head>
<body style="background-color:#212121; font-size:130%;">

<link rel="stylesheet" href="css/main.css">


<?php

/////////////////////////////////////////////////////////////////////////////////////////////////////FORCE PHP TO DISPLAY ERRORS//////////////////////////////////////////////
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/////////////////////////////////////////////////////////////////////////////////////////////////////FORCE PHP TO DISPLAY ERRORS//////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////SUPER GLOBALS//////////////////////////////////////////////
$device_chosen = "";
$tmp_file = "";

$slave_id = "";
$usb = "";
$alias = "";
$parentobj_id = "";
$isvisible = "";
$isenable = "";

$aliasErr = $parentobjErr = $visibleErr = $activerErr = $slaveidErr = $usbErr = "";
/////////////////////////////////////////////////////////////////////////////////////////////////////SUPER GLOBALS//////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////functions//////////////////////////////////////////////

/*
function load_session($dev_name) {
    $ret_parse_ini = parse_ini_file("modbus_py/modbus__cache/session.ini", true);
    if ($ret_parse_ini === false) { echo "parse_ini_file() failed";exit(2);} //EXIT

    $slave_id =
    $usb = 
    $alias = 
    $parentobj_id =
    $isvisible = 
    $isenable = 

}*/



function test_input($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

function spawn_radio_btn($name, $input_name, $value, $checked) {
	echo '	<input type="radio" name=$input_name $checked?> value=$value>$name ';
}



/////////////////////////////////////////////////////////////////////////////////////////////////////functions//////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////////////////////////////validate get back device_chosen get//////////////////////////////////////////////
if (empty($_GET['device_chosen']) == FALSE) {
	//echo "PREMIERE FOIS    ";
	$device_chosen = $_GET['device_chosen'];

	$tmp_file = fopen("tmp_file", "w+");
	if ($tmp_file === false)
		exit(123);
	fwrite($tmp_file, $device_chosen); 
	fclose($tmp_file);
}
else {
	//echo("SECONDE FOIS    ");
	$tmp_file = fopen("tmp_file", "r");
	if ($tmp_file === false)
		exit(123);
	$device_chosen = file_get_contents("tmp_file");
	fclose($tmp_file);
}
/////////////////////////////////////////////////////////////////////////////////////////////////////validate get back device_chosen get//////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////////////////////AFTER VALIDATE//////////////////////////////////////////////


if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    //name="alias" sets in post
    //usb
    if (empty($_POST["usb"])) {$usbErr = "usb is required";} 
    else                      
    {
        $usb = test_input($_POST["usb"]);
        if (preg_match("/[0-4]/", $usb) == 0)
            {$usbErr = "must match between 0 and 4";}
    
    }
    
    //check slavei
    if (empty($_POST["slaveid"])) {$slaveidErr = "sonde id is required";} 
    else                          
    {
        $slaveid = test_input($_POST["slaveid"]);
        $x = (int)$slaveid;
        if (is_numeric($x) === FALSE)
            {$slaveidErr = "has to be a number";}
        else
        {
            if ($x < 1) {$slaveidErr = "has to be 0 - 254";}
            if ($x > 254) {$slaveidErr = "has to be 0 - 254";}
        }
        
    }

    //check alias
	if (empty($_POST["alias"]))
		$aliasErr = "Alias is required";
  	else {
		$alias = test_input($_POST["alias"]);
        if (preg_match("/^[0-9a-zA-Z_-]*$/", $alias) == 0) 
            {$aliasErr = "Only letters and white space allowed";}
  	}

    //check activer
	if (empty($_POST["activer"])) {$activerErr = "Activer is required";} 
  	else                          {$activer = test_input($_POST["activer"]);}

    //check vis
  	if (empty($_POST["visible"])) {$visibleErr = "Visible is required";} 
  	else                          {$visible = test_input($_POST["visible"]);}
	  
	$parentobj_id = test_input($_POST["select_parentobj"]);


if ($aliasErr === "" and $visibleErr === "" and $activerErr === "" and $usbErr === "" and $slaveidErr === "") {/*SAVE SESSION   form_to_db($dbconnect, $device_chosen, $alias, $visible, $activer);*/}
	
} /* if ($_SERVER["REQUEST_METHOD"] == "POST") */


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////START POINT//////////////////////////////////////////////


if ($device_chosen === "") {$device_chosen = "new";}
//else                       {/*load_session($device_chosen);*/}


?>

<h2>Device Configuration : <?php echo $device_chosen; ?></h2>

<p><span class="error">* required field</span></p>

<form id="main_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
    <!-- usb:  -->
    <span class="field_to_fill">usb</span>
	<input class =field_to_fill style=color:black; type="text" name="usb" value="<?php echo '';?>">
	<span class="error">* <?php echo $usbErr;?></span>
    <br><br>
    
    <!-- slave id:  -->
    <span class="field_to_fill">sonde id</span>
	<input class =field_to_fill style=color:black; type="text" name="slaveid" value="<?php echo ''?>">
	<span class="error">* <?php echo $slaveidErr;?></span>
    <br><br>
    
    <!-- Alias: -->
	<span class="field_to_fill">Alias</span>
	<input class =field_to_fill style=color:black; type="text" name="alias" value="<?php echo $alias;?>">
	<span class="error">* <?php echo $aliasErr;?></span>
	<br><br>


    <!-- Activer: -->
	<span class="field_to_fill">Activer
		<input type="radio" name="activer" <?php if (isset($activer) && $activer=="Oui") echo "checked";?> value="Oui">Oui
		<input type="radio" name="activer" <?php if (isset($activer) && $activer=="Non") echo "checked";?> value="Non">Non
	</span>
	<span class="error">* <?php echo $activerErr;?></span>
	<br><br>

	<!-- Visible: -->
	<span class="field_to_fill">Visible 
		<input type="radio" name="visible" <?php if (isset($visible) && $visible=="Oui") echo "checked";?> value="Oui">Oui
		<input type="radio" name="visible" <?php if (isset($visible) && $visible=="Non") echo "checked";?> value="Non">Non
	</span>
	<span class="error">* <?php echo $visibleErr;?></span>
	<br><br>
     
    <!--get from db parentobj -->
	<span class="field_to_fill">Objet parent:</span>
	<select class=select-css name="select_parentobj" id="id_parentobj">
		<option style=font-weight:normal; value="">Aucun</option>
		<?php 
			$dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
			if ($dbconnect->connect_errno) {printf("Connection to 'jeedom' database failed");exit;}
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
    
	<br><br>
	<!-- VALIDER -->
	<input class=button_valider type="submit" name="submit" value="Valider"> 
</form>



<div class=top_left_corner><button class=button_back_to_main type=button onclick="document.location.href='main.php'" >Retourner à la page principale</button></div>
<br><br>
<div class=bottom_right_corner>	<button class=button_destroy type=button onclick="document.location.href='destroy_probe.php'"> Supprimer un équipement modbus </button> </div>



</body>
</html>
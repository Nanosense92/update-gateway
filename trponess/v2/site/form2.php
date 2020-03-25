
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
//session_start();  
/////////////////////////////////////////////////////////////////////////////////////////////////////FORCE PHP TO DISPLAY ERRORS//////////////////////////////////////////////
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/////////////////////////////////////////////////////////////////////////////////////////////////////FORCE PHP TO DISPLAY ERRORS//////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////SUPER GLOBALS//////////////////////////////////////////////
$device_chosen = "";

$slaveid = "";
$usb = "";
$alias = "";
$parentobj_id = "";
$isvisible = "";
$isenable = "";

$aliasErr = $parentobjErr = $isvisibleErr = $isenableErr = $slaveidErr = $usbErr = "";
/////////////////////////////////////////////////////////////////////////////////////////////////////SUPER GLOBALS//////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////////////functions//////////////////////////////////////////////


function save_session($alias, $slaveid, $usb, $isvisible, $isenable, $parentobj_id, $change) {

	if ($change === "") 
		exec("sudo python3 ../modbus_py/session.py add name=$alias slaveid=$slaveid usb=$usb isvisible=$isvisible isenable=$isenable parentobj_id=$parentobj_id 2>&1", $output, $return_value);
	else
	{
		
		exec("sudo python3 ../modbus_py/session.py add change=$change name=$alias slaveid=$slaveid usb=$usb isvisible=$isvisible isenable=$isenable parentobj_id=$parentobj_id 2>&1", $output, $return_value);
	}
	$ret_parse_ini = parse_ini_file("../modbus_py/modbus__cache/session.ini", true);
	if ($ret_parse_ini === false) {echo "parse_ini_file(session) failed"; exit(2);}
}

function load_session($device_chosen) {
    $ret_parse_ini = parse_ini_file("../modbus_py/modbus__cache/session.ini", true);
    if ($ret_parse_ini === false) { echo "parse_ini_file() failed";exit(2);}
    #var_dump($ret_parse_ini);
    foreach ($ret_parse_ini as $k => $v) {
        $dict = $v;
		$section = $k;
		
		//echo "?? " . $section . '==' . $device_chosen . "<br>";
		if ($section === $device_chosen) {
			return $v;
		}
	}
}

/*
function init_data($dict, $alias, $slaveid, $usb, $isvisible, $isenable, $parentobj_id) {

	$alias = $dict['name'];
	$slaveid = $dict['slaveid'];
	$isvisible = $dict['isvisible'];
	$isenable = $dict['isenable'];
	$parentobj_id = $dict['parentobj_id'];
}
*/

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
/*if ($_GET['device_chosen'] !== "") {
	//echo "PREMIERE FOIS    ";
	$device_chosen = $_GET['device_chosen'];
	$_GET['device_chosen'] = $_SESSION['device_chosen']
}
else {
	$_SESSION['device_chosen']
}
	
	$tmp_file = fopen("tmp_file", "w+");
	chmod($tmp_file, 0777);
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
}*/
/////////////////////////////////////////////////////////////////////////////////////////////////////validate get back device_chosen get//////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////////////////////AFTER VALIDATE//////////////////////////////////////////////


if ($_SERVER["REQUEST_METHOD"] == "POST") 
{

	
	$device_chosen = $_POST['device_chosen'];
	
    //name="alias" sets in post
    //usb
    if ($_POST["usb"] === "") {$usbErr = "usb is required";} 
    else                      
    {
        $usb = test_input($_POST["usb"]);
        if (preg_match("/[0-4]/", $usb) == 0)
            {$usbErr = "must match between 0 and 4";}
    
    }
    
    //check slavei
    if ($_POST["slaveid"] === "") {$slaveidErr = "sonde id is required";} 
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
	if ($_POST["alias"] === "")
		$aliasErr = "Alias is required";
  	else {
		$alias = test_input($_POST["alias"]);
        if (preg_match("/^[0-9a-zA-Z_-]*$/", $alias) == 0) 
            {$aliasErr = "Only letters and white space allowed";}
  	}

    //check isenable
	if ($_POST["isenable"] === "") {$isenableErr = "isenable is required";} 
  	else                          {$isenable = test_input($_POST["isenable"]);}

    //check vis
  	if ($_POST["isvisible"] === "") {$isvisibleErr = "isvisible is required";} 
  	else                          {$isvisible = test_input($_POST["isvisible"]);}
	  
	$parentobj_id = test_input($_POST["parentobj_id"]);


if ($aliasErr === "" and $isvisibleErr === "" and $isenableErr === "" and $usbErr === "" and $slaveidErr === "") 
	{
		
		save_session($_POST["alias"], $_POST["slaveid"], $_POST["usb"], $_POST["isvisible"], $_POST["isenable"], $_POST["parentobj_id"], $device_chosen);
		echo "<script> window,alert(\"SAVED going back to main page\"); </script>";
		echo "<script> document.location.href='main2.php'; </script>";
	}


	
} /* if ($_SERVER["REQUEST_METHOD"] == "POST") */


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////START POINT//////////////////////////////////////////////


if ($_GET['device_chosen'] === "") {$device_chosen = "";}
else                       
{
	$device_chosen = $_GET['device_chosen'];
	$dict = load_session($device_chosen);

	
	
	$alias = $dict['name'];
	$usb = $dict['usb'];
	$slaveid = $dict['slaveid'];
	$isvisible = $dict['isvisible'];
	$isenable = $dict['isenable'];
	$parentobj_id = $dict['parentobj_id'];
}



?>

<h2>Device Configuration : <?php echo $device_chosen; ?></h2>

<p><span class="error">* required field</span></p>

<form id="main_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
    <!-- usb:  -->
    <span class="field_to_fill">usb</span>
	<input class =field_to_fill style=color:black; type="text" name="usb" value="<?php echo $usb;?>">
	<span class="error">* <?php echo $usbErr;?></span>
    <br><br>
    
    <!-- slave id:  -->
    <span class="field_to_fill">sonde id</span>
	<input class =field_to_fill style=color:black; type="text" name="slaveid" value="<?php echo $slaveid;?>">
	<span class="error">* <?php echo $slaveidErr;?></span>
    <br><br>
    
    <!-- Alias: -->
	<span class="field_to_fill">Alias</span>
	<input class =field_to_fill style=color:black; type="text" name="alias" value="<?php echo $alias;?>">
	<span class="error">* <?php echo $aliasErr;?></span>
	<br><br>


    <!-- isenable: -->
	<span class="field_to_fill">isenable
		<input type="radio" name="isenable" <?php if (isset($isenable) && $isenable=="1") echo "checked";?> value="1">Oui
		<input type="radio" name="isenable" <?php if (isset($isenable) && $isenable=="0") echo "checked";?> value="0">Non
	</span>
	<span class="error">* <?php echo $isenableErr;?></span>
	<br><br>

	<!-- isvisible: -->
	<span class="field_to_fill">isvisible 
		<input type="radio" name="isvisible" <?php if (isset($isenable) && $isvisible=="1") echo "checked";?> value="1">Oui
		<input type="radio" name="isvisible" <?php if (isset($isenable) && $isvisible=="0") echo "checked";?> value="0">Non
	</span>
	<span class="error">* <?php echo $isvisibleErr;?></span>
	<br><br>
     
    <!--get from db parentobj -->
	<span class="field_to_fill">Objet parent:</span>
	<select class=select-css name="parentobj_id" id="id_parentobj">
		<option style=font-weight:normal; value=""><?php echo $parentobj_id; ?></option>
		<?php 
			$dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
			if ($dbconnect->connect_errno) {printf("Connection to 'jeedom' database failed");exit;}
			$ret_query = $dbconnect->query("SELECT id, name FROM object ORDER BY position");
			while ($ret_query_row = $ret_query->fetch_array(MYSQLI_BOTH)) {
				//var_dump($ret_query_row);
				$tmp_objparent_name = $ret_query_row["name"];
				//$tmp_objparent_id = $ret_query_row["id"];
				$parentobj_id = $tmp_objparent_name;
				echo "<option value=\"$parentobj_id\">$parentobj_id</option>";
				
			}
			mysqli_close($dbconnect);  
		?>
	</select>
	
	<input type="hidden" name="device_chosen" value=<?php echo $device_chosen;?> >
	
    
	<br><br>
	<!-- VALIDER -->
	<input class=button_valider type="submit" name="submit" value="Valider"> 
</form>



<div class=top_left_corner><button class=button_back_to_main type=button onclick="document.location.href='main2.php'" >Retourner à la page principale</button></div>
<br><br>
<div class=bottom_right_corner>	<button class=button_destroy type=button onclick="document.location.href='delete_session.php?name=<?php echo $device_chosen;?>'"> Supprimer un équipement modbus </button> </div>



</body>
</html>
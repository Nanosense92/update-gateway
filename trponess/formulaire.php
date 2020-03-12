
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

	h2 {
		color: grey;
		text-decoration: underline overline;
	}
</style>
</head>
<body style="background-color:#212121; font-size:130%;">

<link rel="stylesheet" href="css/main.css">


<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$device_chosen = "";
$tmp_file = "";


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


// define variables and set to empty values
$aliasErr = $parentobjErr = $visibleErr = $activerErr = "";
$alias = $parentobj_id = $visible = $activer = "";

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
		$alias = test_input($_POST["alias"]);
		if (preg_match("/^[0-9a-zA-Z_-]*$/", $alias) == 0) {
	  		$aliasErr = "Only letters and white space allowed";
		}
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

	// echo "<br><br><h3>DEBUG:</h3>";
	// echo "alias = $alias<br>";
	// echo "activer = $activer<br>";
	// echo "visible = $visible<br>";
	// echo "parentobj = $parentobj_id<br>";

	$dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
	if ($dbconnect->connect_errno) {
		printf("Connection to 'jeedom' database failed");
		exit;
	}
	
		
	function form_to_db($dbconnect, $device_chosen,  $alias, $visible, $activer)
	{
		$isVisible = ($visible === "Oui") ? (1) : (0);
		$isEnable = ($activer === "Oui") ? (1) : (0);
		
		//$dbconnect->query("INSERT INTO eqLogic (`name`,isVisible, isEnable) VALUES ('$tmp[0]', '$tmp[1]', '$tmp[2]')");
		$dbconnect->query("UPDATE eqLogic SET name=\"$alias\" WHERE name=\"$device_chosen\"");
		$dbconnect->query("UPDATE eqLogic SET isVisible=$isVisible WHERE name=\"$alias\"");	
		$dbconnect->query("UPDATE eqLogic SET isEnable=$isEnable WHERE name=\"$alias\"");

		echo "<script>
				alert('Configuration sauvegardée\\nVous allez être redirigé vers la page d\'accueil de la configuration modbus');
				document.location.href = 'main.php';
			</script>";
	}

	
	if ($aliasErr === "" and $visibleErr === "" and $activerErr === "") {
		form_to_db($dbconnect, $device_chosen, $alias, $visible, $activer);
	}
	
	mysqli_close($dbconnect);  			 		

} /* if ($_SERVER["REQUEST_METHOD"] == "POST") */


?>



<h2>Device Configuration : <?php echo $device_chosen; ?></h2>
<p><span class="error">* required field</span></p>

<form id="main_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
	<!-- Alias:  -->
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
	 
	<span class="field_to_fill">Objet parent:</span>
	<select class=select-css name="select_parentobj" id="id_parentobj">
		<option style=font-weight:normal; value="">Aucun</option>
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

	<br><br>
	<!-- VALIDER -->
	<input class=button_valider type="submit" name="submit" value="Valider"> 
</form>

<div class=top_left_corner>
	<button class=button_back_to_main type=button onclick="document.location.href='main.php'" >
		Retourner à la page principale
	</button>
</div>

<br>
<br>
<div class=top_left_corner>
	<?php /*$web = "destroy_probe.php?alias=\"$GET['alias']\" "; echo $web;*/?>
	<?php 
		$ali = $_GET['device_chosen']; 
		$web = "destroy_probe.php?alias=\"$ali\" "; 
	?>
	
	<button class=button_back_to_main type=button onclick="document.location.href='destroy_probe.php?alias=<?php echo $ali ?>'">
		destroy
	</button>
</div>


</body>
</html>


<?php
/*
 * script that create a json fromated variable for google chart
 * */
$option = "None";


$db =  mysqli_connect('localhost','jeedom','85522aa27894d77','jeedom');
if ($db->connect_errno){
	printf("connection failed to db");
	exit();
}

if(isset($_POST['submit'])) {
	$valid = 1;
	$option = $_POST['select_loc'];
	$query = "SELECT datetime as TIME,productivity as p,health as h,sleep as s,irritation as i,noise as n FROM impact WHERE location = '" . $option . "' and datetime > DATE_SUB(CURDATE(),INTERVAL 1 MONTH)";
	$results = $db->query($query);
}
else
{
	$valid = 0;
	$results = NULL;
}
$rows = array();
$table = array();

$table['cols'] = array(
	array(
		'label' => 'Date time',
		'type' => 'datetime'
	),
	array(
		'label' => 'Productivity (%)',
		'type' => 'number'
	),
	array(
		'label' => 'Health (%)',
		'type' => 'number'
	),
	array(
		'label' => 'Sleep quality (%)',
		'type' => 'number'
	),
	array(
		'label' => 'Irritation (%)',
		'type' => 'number'
	),
	array(
		'label' => 'Noise Comfort (%)',
		'type' => 'number'
	)
);

if ($valid != 0) {
	while($row = $results->fetch_array(MYSQLI_BOTH)){
		$sub_array = array();
		$datetime = explode(".", $row["TIME"]);
		$sub_array[] = array(
			"v" => 'Date(' . Date("Y,m,d,H,i,s", strtotime($row["TIME"] . '-1 months')) . ')'
		);
		if (strcmp($row["p"], $diff) == 0) { $row["p"] = NULL; }
		$sub_array[] = array(
			"v" => $row["p"]
		);
		if ($row["h"] < 0) { $row["h"] = NULL; }
		$sub_array[] = array(
			"v" => $row["h"]
		);
		if ($row["s"] < 0) { $row["s"] = NULL; }
		$sub_array[] = array(
			"v" => $row["s"]
		);
		if ($row["i"] < 0) { $row["i"] = NULL; }
		$sub_array[] = array(
			"v" => $row["i"]
		);
		if ($row["n"] < 0) { $row["n"] = NULL; }
		$sub_array[] = array(
			"v" => $row["n"]
		);
		$rows[] = array(
			"c" => $sub_array
		);
	}
}
$table['rows'] = $rows;
$jsontable = json_encode($table, JSON_PRETTY_PRINT);

$name_table = $option;

?>

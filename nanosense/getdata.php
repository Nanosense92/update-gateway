<?php
/*
 * script that creat a formated json for google chart 
 * */
$dataoption = "None";


$datadb =  mysqli_connect('localhost','jeedom','85522aa27894d77','jeedom'); //connect to BD
if ($datadb->connect_errno){
	printf("connection failed to db");
	exit();
}

if(isset($_POST['submit'])) {
	$datavalid = 1;
	$dataoption = $_POST['select_loc']; //get the post value for the query
	$dataquery = "SELECT history.datetime,history.value,cmd.name,cmd.unite,object.name from history,cmd,object,eqLogic WHERE  history.cmd_id = cmd.id and cmd.eqLogic_id = eqLogic.id and eqLogic.object_id = object.id and object.name = '" . $dataoption . "' and minute(datetime) % 5 = 0 UNION SELECT historyArch.datetime,historyArch.value,cmd.name,cmd.unite,object.name from historyArch,cmd,object,eqLogic WHERE  historyArch.cmd_id = cmd.id and cmd.eqLogic_id = eqLogic.id and eqLogic.object_id = object.id and object.name = '" . $dataoption . "' and minute(datetime) % 5 = 0 and datetime > DATE_SUB(CURDATE(), INTERVAL 1 MONTH) order by datetime";
	$dataresults = $datadb->query($dataquery);
}
else
{
	$datavalid = 0;
	$dataresults = NULL;
}
$datarows = array();
$datatable = array();

$datatable['cols'] = array(
	array(
		'label' => 'Date time',
		'type' => 'datetime'
	),
	array(
		'label' => 'température',
		'type' => 'number'
	),
	array(
		'label' => 'Humidity',
		'type' => 'number'
	),
	array(
		'label' => 'CO2',
		'type' => 'number'
	),

	array(
		'label' => 'PM2.5',
		'type' => 'number'
	),
	array(
		'label' => 'PM10',
		'type' => 'number'
	),
	array(
		'label' => 'PM1',
		'type' => 'number'
	),
	array(
		'label' => 'VOC',
		'type' => 'number'
	)
);

$timetrig = 1;
$timedate = NULL;
$TEMP = NULL;
$HUM = NULL;
$CO2 = NULL;
$PM25 = NULL;
$PM10 = NULL;
$PM1 = NULL;
$COV = NULL;

if ($datavalid != 0) {
	while($datarow = $dataresults->fetch_array(MYSQLI_BOTH)){
		if ($timetrig == 1)
		{
			$timedate = Date("Y,m,d,H,i", strtotime($datarow[0]));
			$timetrig = 0;
		}
		if (strcmp($datarow[2], "Température") == 0){
			$TEMP = $datarow[1];}
		if (strcmp($datarow[2], "Humidité") == 0){
			$HUM = $datarow[1];}
		if (strcmp($datarow[2], "CO2") == 0){
			$CO2 = $datarow[1];}
		if (strcmp($datarow[2], "PM2.5") == 0){
			$PM25 = $datarow[1];}
		if (strcmp($datarow[2], "PM10") == 0){
			$PM10 = $datarow[1];}
		if (strcmp($datarow[2], "PM1") == 0){
			$PM1 = $datarow[1];}
		if (strcmp($datarow[2], "Total") == 0){
			$COV = $datarow[1];}

		if (Date($timedate < Date("Y,m,d,h,i", strtotime($datarow[0])))){
			$sub_array = array();
			$sub_array[] = array(
				"v" => 'Date(' . Date("Y,m,d,H,i,s", strtotime($datarow[0] . '-1 months')) . ')'
			);
			$sub_array[] = array(
				"v" => $TEMP
			);
			$sub_array[] = array(
				"v" => $HUM
			);
			$sub_array[] = array(
				"v" => $CO2
			);
			$sub_array[] = array(
				"v" => $PM25
			);
			$sub_array[] = array(
				"v" => $PM10
			);
			$sub_array[] = array(
				"v" => $PM1
			);
			$sub_array[] = array(
				"v" => $COV
			);
			$datarows[] = array(
				"c" => $sub_array
			);
			$timedate = Date("Y,m,d,H,i", strtotime($datarow[0]));
/*			$TEMP = NULL;
			$HUM = NULL;
			$CO2 = NULL;
			$PM25 = NULL;
			$PM10 = NULL;
			$PM1 = NULL;
			$COV = NULL;	*/
		}
	}
}
$datatable['rows'] = $datarows;
$datajsontable = json_encode($datatable, JSON_PRETTY_PRINT);

$dataname_table = $dataoption;


?>

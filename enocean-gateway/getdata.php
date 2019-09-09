<?php

$hostname = "localhost";
$username = "jeedom";
$password = "85522aa27894d77";
$db = "jeedom";


$dbconnect =   mysqli_connect($hostname,$username,$password,$db);

$logfile = fopen("getdata.log", "w");

if ($dbconnect->connect_errno){
	printf("connect failed");
	exit();
}
$result = $dbconnect->query("SELECT  eqLogic.name as alias,eqLogic.logicalId,cmd.name,history.value,cmd.unite,history.datetime,cmd.id FROM history,cmd,eqLogic WHERE (datetime,cmd_id) IN (SELECT MAX(datetime),cmd_id FROM history group by cmd_id) AND history.cmd_id = cmd.id AND cmd.eqLogic_id = eqLogic.id GROUP BY cmd_id");

$location = "";
$rows = array();
$table = array();
$hotfix = "";
while ($row = $result->fetch_array(MYSQLI_BOTH)) {
	fwrite($logfile, date("Y-m-d H:i:s") . "\t");
	fwrite($logfile, $row[0] . "\n");
	$value_array = array();
	if ($row[2] == "PM2.5")
		$hotfix = "PM2_5";
	else if (strpos($row[2], "Temp") !== false)
		$hotfix = "TMP";
	else if (strpos($row[2], "Hum") !== false)
		$hotfix = "HUM";
	else if (strpos($row[2], "Total") !== false)
		$hotfix = "VOC";
	else
		$hotfix = $row[2];
	$idquery = $dbconnect->query("SELECT * FROM history WHERE cmd_id='" . $row[6] . "' AND datetime >= ADDTIME(now(), '01:55:00')");
	while ($valquery = $idquery->fetch_array(MYSQLI_BOTH)){
		$value_array[] = array(
			"at" => date("Y-m-d\TH:i:s\Z", strtotime($valquery[1] . '-2 hours')),
			"value" => $valquery[2]);
		//	var_dump($value_array);
	}
	$table['version'] = "1.0.0";
	$table['datastreams'] = array(
		array(
			'alias'=> $row[0] . '-' . $hotfix,
			'location'=> $location,
			'pollutant'=> $row[2],
			'id'=> $row[1],
			'datapoints' => $value_array
		)
	);
	//http request with database query
	$http_res = $dbconnect->query("SELECT * FROM nanodb");
	while ($httpreq = $http_res->fetch_array(MYSQLI_BOTH)) {
		$table['datastreams'][0]['location'] = $httpreq['location'];
		$url = $httpreq['addr'] . ':' . $httpreq['port'] . '/' . $httpreq['path'];
		fwrite($logfile, $url . "\t");
		$ch = curl_init($url);
		$login = $httpreq['login'];
		$pass = $httpreq['password'];
		var_dump($url);
		$jsondata = json_encode($table, JSON_PRETTY_PRINT);
		echo $jsondata;
		echo "\n";

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_USERPWD, "$login:$pass");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$res = curl_exec($ch);
		fwrite($logfile, $res . "\n");
		var_dump($res);
		if (curl_errno($ch))
		{
			echo 'error:'. curl_error($ch);
		}
		curl_close($ch);
	}
}
fclose($logfile);
mysqli_close($dbconnect);
?>

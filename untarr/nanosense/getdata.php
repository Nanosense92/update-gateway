<?php
/*
 * script that create a formated json for google chart 
 * */
$dataoption = 'None';
$datadb = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom'); //connect to DB
if ($datadb->connect_errno){
	printf('connection failed to db');
	exit;
}

$sqldateformat = 'None';

if(isset($_POST['submit'])){
	$datavalid = 1;
    $dataoption = $_POST['select_loc'];

    $start_date2 = $_POST['start_date'] . ' 00:00:00';
    $end_date2 = $_POST['end_date'] . ' 23:59:59';
    $start_datetime2 = new Datetime($start_date2);
    $end_datetime2 = new Datetime($end_date2);
    $datainterval = date_diff($start_datetime2, $end_datetime2);
    
    if ($datainterval->m == 0 && $datainterval->d <= 6){
        $sqldateformat = '%Y-%m-%d %H:%i'; // (each minute)
    }
    elseif ($datainterval->m == 0 && $datainterval->d > 6 && $datainterval->d <= 30){
        $sqldateformat = '%Y-%m-%d %H'; // (each hour)
    }
    else{
        $sqldateformat = '%Y-%m-%d'; // (each day)
    }    
    $basequery = "SELECT date_format(history.datetime, \"" . $sqldateformat . "\") AS date, " .
        "history.value, cmd.name AS cmd_name, cmd.unite, object.name FROM history, " .
        "cmd, object, eqLogic WHERE history.cmd_id = cmd.id AND cmd.eqLogic_id = eqLogic.id " .
        "AND eqLogic.object_id = object.id AND object.name = '" . $dataoption . "' " .
        "AND datetime > '" . $start_date2 . "' AND datetime < '" . $end_date2 . "' " .
        "UNION ALL SELECT date_format(historyArch.datetime, \"" . $sqldateformat . "\") AS date, " .
        "historyArch.value, cmd.name AS cmd_name, cmd.unite, object.name " .
        "FROM historyArch, cmd, object, eqLogic WHERE historyArch.cmd_id = cmd.id AND cmd.eqLogic_id = eqLogic.id " .
        "AND eqLogic.object_id = object.id AND object.name = '" . $dataoption . "' " .
        "AND datetime > '" . $start_date2 . "' AND datetime < '" . $end_date2 . "' GROUP BY cmd_name, date ORDER BY date";
    
    // query that compute the average of each physical measure (temperature, humidity, etc...) at every interval of time defined in sqldateformat
    //$averagequery = 'SELECT date, avg(value), cmd_name, unite FROM (' . $basequery . ') AS average GROUP BY cmd_name, date ORDER BY date';
    //var_dump($averagequery);
    
    $dataresults = $datadb->query($basequery);
    //var_dump($dataresults->fetch_all(MYSQLI_BOTH));
}
else{
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
		'label' => 'Temperature (°C)',
		'type' => 'number'
	),
	array(
		'label' => 'Humidity (%)',
		'type' => 'number'
	),
	array(
		'label' => 'CO2 (ppm)',
		'type' => 'number'
	),
	array(
		'label' => 'PM2.5 (µg/m3)',
		'type' => 'number'
	),
	array(
		'label' => 'PM10 (µg/m3)',
		'type' => 'number'
	),
	array(
		'label' => 'PM1 (µg/m3)',
		'type' => 'number'
	),
	array(
		'label' => 'VOC (µg/m3)',
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

if ($datavalid == 1){
    while($datarow = $dataresults->fetch_array(MYSQLI_BOTH)){
        if (strcmp($sqldateformat, '%Y-%m-%d %H:%i') == 0){
            $jsondateformat = Date('Y,m,d,H,i,s', strtotime($datarow[0] . ':00 -1 months'));
        }
        elseif (strcmp($sqldateformat, '%Y-%m-%d %H') == 0){
            $jsondateformat = Date('Y,m,d,H,i,s', strtotime($datarow[0] . ':00:00 -1 months'));
        }
        else{
            $jsondateformat = Date('Y,m,d,H,i,s', strtotime($datarow[0] . ' 00:00:00 -1 months'));
        }

        if (strcmp($datarow[2], 'Température') == 0){
            $TEMP = $datarow[1];
        }
        else if (strcmp($datarow[2], 'Humidité') == 0){
            $HUM = $datarow[1];
        }
        else if (strcmp($datarow[2], 'CO2') == 0){
            $CO2 = $datarow[1];
        }
        else if (strcmp($datarow[2], 'PM2.5') == 0){
            $PM25 = $datarow[1];
        }
        else if (strcmp($datarow[2], 'PM10') == 0){
            $PM10 = $datarow[1];
        }
        else if (strcmp($datarow[2], 'PM1') == 0){
            $PM1 = $datarow[1];
        }
        else if (strcmp($datarow[2], 'Total') == 0){
            $COV = $datarow[1];
        }
        if ($timetrig == 0 && $timedate != $jsondateformat){
            $sub_array = array();
            $sub_array[] = array(
                'v' => 'Date(' . $timedate . ')'
            );
            $sub_array[] = array(
                'v' => $TEMP
            );
            $sub_array[] = array(
                'v' => $HUM
            );
            $sub_array[] = array(
                'v' => $CO2
            );
            $sub_array[] = array(
                'v' => $PM25
            );
            $sub_array[] = array(
                'v' => $PM10
            );
            $sub_array[] = array(
                'v' => $PM1
            );
            $sub_array[] = array(
                'v' => $COV
            );
            $datarows[] = array(
                'c' => $sub_array
            );
            $timedate = $jsondateformat;
        }       
        if ($timetrig == 1){
            $timedate = $jsondateformat;
			$timetrig = 0;
		}
	}
}
$datatable['rows'] = $datarows;
$datajsontable = json_encode($datatable, JSON_PRETTY_PRINT);

$dataname_table = $dataoption;
$datadb->close();
?>

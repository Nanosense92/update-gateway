<?php
/*
 * script that create a json fromated variable for google chart
 * */
$option = 'None';
$db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');
if ($db->connect_errno){
	printf('connection failed to db');
	exit;
}

if(isset($_POST['submit'])){
	$valid = 1;
    $option = $_POST['select_loc'];

    $start_date = $_POST['start_date'] . ' 00:00:00';
    $end_date = $_POST['end_date'] . ' 23:59:59';
    $start_datetime = new Datetime($start_date);
    $end_datetime = new Datetime($end_date);
    $interval = date_diff($start_datetime, $end_datetime);
    
    if ($interval->m == 0 && $interval->d <= 6){
        $sqldateformat = '%Y-%m-%d %H:%i'; // (each minute)
    }
    elseif ($interval->m == 0 && $interval->d > 6 && $interval->d <= 30){
        $sqldateformat = '%Y-%m-%d %H'; // (each hour)
    }
    else{
        $sqldateformat = '%Y-%m-%d'; // (each day)
    }
    $query = "SELECT date_format(datetime, \"" . $sqldateformat . "\") AS date, avg(productivity) AS p, " .
        "avg(health) AS h, avg(sleep) AS s, avg(irritation) AS i, avg(noise) AS n FROM impact " .
        "WHERE location = '" . $option . "' AND datetime > '" . $start_date . "' AND datetime < '" . $end_date .
        "' GROUP BY date ORDER BY date";
	$results = $db->query($query);
}
else{
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

$trig = 1;
$timedate = NULL;
$date = NULL;
$PROD = NULL;
$HEAL = NULL;
$SLEEP = NULL;
$IRRI = NULL;
$NOISE = NULL;

if ($valid == 1) {
	while($row = $results->fetch_array(MYSQLI_BOTH)){
        if (strcmp($sqldateformat, '%Y-%m-%d %H:%i') == 0){
            $jsondateformat = Date('Y,m,d,H,i,s', strtotime($row[0] . ':00 -1 months'));
        }
        elseif (strcmp($sqldateformat, '%Y-%m-%d %H') == 0){
            $jsondateformat = Date('Y,m,d,H,i,s', strtotime($row[0] . ':00:00 -1 months'));
        }
        else{
            $jsondateformat = Date('Y,m,d,H,i,s', strtotime($row[0] . ' 00:00:00 -1 months'));
        }
        $PROD = $row['p'];
        $HEAL = $row['h'];
        $SLEEP = $row['s'];
        $IRRI = $row['i'];
        $NOISE = $row['n'];

        if ($trig == 0 && $timedate != $jsondateformat)
        {
            $sub_array = array();
            $sub_array[] = array(
                'v' => 'Date(' . $timedate . ')'
            );
            if ($PROD < 0){
                $PROD = NULL;
            } 
            $sub_array[] = array(
                'v' => $PROD
            );
            if ($HEAL < 0){
                $HEAL = NULL;
            }
            $sub_array[] = array(
                'v' => $HEAL
            );
            if ($SLEEP < 0){
                $SLEEP = NULL;
            }
            $sub_array[] = array(
                'v' => $SLEEP
            );
            if ($IRRI < 0){
                $IRRI = NULL;
            }
            $sub_array[] = array(
                'v' => $IRRI
            );
            if ($NOISE < 0){
                $NOISE = NULL;
            }
            $sub_array[] = array(
                'v' => $NOISE
            );
            $rows[] = array(
                'c' => $sub_array
            );
            $timedate = $jsondateformat;
        }
        if ($trig == 1){
            $timedate = $jsondateformat;
            $trig = 0;
        }
    }
}
$table['rows'] = $rows;
$jsontable = json_encode($table, JSON_PRETTY_PRINT);

$name_table = $option;
$db->close();
?>

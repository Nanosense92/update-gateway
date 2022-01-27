<?php

//require_once "/home/pi/enocean-gateway/get_database_password.php";

// $host = 'localhost';
// $user = 'jeedom';
// $pass = $jeedom_db_passwd;
// $dbname = 'jeedom';

// $db = mysqli_connect($host, $user, $pass, $dbname);
// $results = $db->query('SELECT * FROM nanodb');



$push_infos_array = file("/var/www/html/nanosense/pushtocloud.conf", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($push_infos_array === false) {
    echo "FATAL ERROR: failed to open file pushtocloud.conf (2)\n";
    exit ;
}


$rows = array();
$table = array();

$table['cols'] = array(
    array(
        'label' => 'ID',
        'type' => 'string'
    ),
    array(
        'label' => 'login',
        'type' => 'string'
    ),
    array(
        'label' => 'password',
        'type' => 'string'
    ),
    array(
        'label' => 'addr',
        'type' => 'string'
    ),
    array(
        'label' => 'port',
        'type' => 'string'
    ),
    array(
        'label' => 'path',
        'type' => 'string'
    ),
    array(
        'label' => 'location',
        'type' => 'string'
    )
);

$nb_lines = count($push_infos_array);
for ($i = 0 ; $i < $nb_lines ; $i++) {
//while($row = $results->fetch_array(MYSQLI_BOTH)){

    $exploded = explode(' ', $push_infos_array[$i]);
    // retirer les quotes
    for ($j = 0 ; $j < count($exploded) ; $j++) {
        $exploded[$j] = trim($exploded[$j], "'");
    }

    $sub_array = array();
    $sub_array[] = array(
        'v' => $i,
    );
    //echo "ROW 0  " . $i . "\n";

    $sub_array[] = array(
        'v' => $exploded[0],
    );
   //echo "ROW 1  " . $exploded[0] . "\n";

    $sub_array[] = array(
        'v' => $exploded[1],
    );
    //echo "ROW 2  " . $exploded[1] . "\n";

    $sub_array[] = array(
        'v' => $exploded[2],
    );
    //echo "ROW 3  " . $exploded[2] . "\n";

    $sub_array[] = array(
        'v' => $exploded[3],
    );
    //echo "ROW 4  " . $exploded[3] . "\n";

    $sub_array[] = array(
        'v' => $exploded[4],
    );
    //echo "ROW 5  " . $exploded[4] . "\n";

    $sub_array[] = array(
        'v' => $exploded[5],
    );
    //echo "ROW 6  " . $exploded[5] . "\n";

    $rows[] = array(
        'c' => $sub_array
    );
}
$table['rows'] = $rows;
echo json_encode($table, JSON_PRETTY_PRINT);

//print_r($table);

?>

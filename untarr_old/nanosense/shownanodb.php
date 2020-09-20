<?php

$host = 'localhost';
$user = 'jeedom';
$pass = '85522aa27894d77';
$dbname = 'jeedom';

$db = mysqli_connect($host, $user, $pass, $dbname);
$results = $db->query('SELECT * FROM nanodb');
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


while($row = $results->fetch_array(MYSQLI_BOTH)){
    $sub_array = array();
    $sub_array[] = array(
        'v' => $row[0],
    );
    $sub_array[] = array(
        'v' => $row[1],
    );
    $sub_array[] = array(
        'v' => $row[2],
    );
    $sub_array[] = array(
        'v' => $row[3],
    );
    $sub_array[] = array(
        'v' => $row[4],
    );
    $sub_array[] = array(
        'v' => $row[5],
    );
    $sub_array[] = array(
        'v' => $row[6],
    );
    $rows[] = array(
        'c' => $sub_array
    );
}
$table['rows'] = $rows;
echo json_encode($table, JSON_PRETTY_PRINT);
?>

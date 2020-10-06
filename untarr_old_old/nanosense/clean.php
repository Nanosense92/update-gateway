<?php
/*
 * clean all none existant name or location from impact table
 * */
$db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');// connect to the database

if($db->connect_errno){
    echo 'connection to db failed'; 
    exit;
}

$query = 'SELECT name FROM object';
$query2 = 'SELECT DISTINCT location FROM impact';
$result2 = $db->query($query2);

while ($row = $result2->fetch_array(MYSQLI_BOTH)){
    $trig = 0;
    $result = $db->query($query);
    while ($imp = $result->fetch_array(MYSQLI_BOTH)){
        if (strcmp($row['location'], $imp['name']) == 0){
            $trig = 1;
        }
    }
    if ($trig !== 1 && strcmp($row['location'], 'default') !== 0){
        $delquery = "DELETE FROM impact WHERE location ='" . $row['location'] . "'";
        echo $delquery;
        $delog = $db->query($delquery);
    }
}
mysqli_close($db);
header('Location:main.php');
exit;
?>

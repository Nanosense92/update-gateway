<?php
include 'getsettings.php';

$create_table_query = "CREATE TABLE IF NOT EXISTS "
    . "`impact` (`datetime` TIMESTAMP, `id` VARCHAR(255), "
    . "`location` VARCHAR(255), `productivity` VARCHAR(255), "
    . "`health` VARCHAR(255), `sleep` VARCHAR(255), "
    . "`irritation` VARCHAR(255), `noise` VARCHAR(255))";
$dblog = $dbconnect->query($create_table_query);

$loc_query = "SELECT DISTINCT `location` FROM `impact`";
$result = $dbconnect->query($loc_query);
while ($loc_row = $result->fetch_array(MYSQLI_BOTH)){
    $insert_query = "INSERT INTO `impact` "
        . "VALUES(ADDTIME(NOW(), '$offset'), '0000' , '$loc_row[0]', "
        . "'NULL', 'NULL', 'NULL', 'NULL', 'NULL')";
    $dblog = $dbconnect->query($insert_query);
    //var_dump($dblog);
}
mysqli_close($dbconnect);
?>

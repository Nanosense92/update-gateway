<?php

$errors = array();


// connect to the database
$db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');

if($db->connect_errno){
	echo "connection to db failed"; 
	exit (); }

$create_table = "CREATE TABLE IF NOT EXISTS impact (datetime TIMESTAMP, id varchar(255), location varchar(255), productivity varchar(255), health  varchar(255), sleep varchar(255),  irritation varchar(255),  noise varchar(255))";
$dblog = mysqli_query($db,$create_table);



$query = "SELECT DISTINCT location from impact";
$result = $db->query($query);
while ($row = $result->fetch_array(MYSQLI_BOTH)) {

  	$query = "INSERT INTO impact VALUES(addtime(now(),'02:00:00'),'0000','$row[0]','NULL','NULL','NULL','NULL','NULL')";
	$dblog = mysqli_query($db,$query);
	var_dump($dblog);
}
?>

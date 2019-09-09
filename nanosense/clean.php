<?php
/*
 * clean all none existant name or location from impact table
 * */
$db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');// connect to the database

if($db->connect_errno){
	echo "connection to db failed"; 
	exit (); }

	$query = "select name from object";
$query2 = "select distinct location from impact";
$result2 = $db->query($query2);


while ($row = $result2->fetch_array(MYSQLI_BOTH)) {
	$trig = 0;
	$result = $db->query($query);
	while ($imp = $result->fetch_array(MYSQLI_BOTH)) {
		if (strcmp($row[0], $imp[0]) == 0) {
			$trig = 1;
		}
	}
	echo $trig;
	if ($trig !== 1 && strcmp($row["location"], "default") !== 0)
	{
		$delquery = "delete from impact where location ='" . $row["location"] . "'";
		echo $delquery;
		$delog = $db->query($delquery);
		var_dump($delog);
	}

}
	header("Location:main.php");
exit;
?>

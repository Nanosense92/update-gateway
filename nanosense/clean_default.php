<?php
/*
 * script cleaning impact value without assigned place
 * */


$db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');// connect to the database
if($db->connect_errno){
	echo "connection to db failed"; 
	exit (); }

		$delquery = "delete from impact where location ='default'";
		echo $delquery;
		$delog = $db->query($delquery);
		var_dump($delog);

	header("Location:main.php");
exit;
?>

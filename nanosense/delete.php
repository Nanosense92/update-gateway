<?php
/*
 * script to delete line in nandb who contain reference for http post
 * */
$id = "";
$db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');// connect to the database
if($db->connect_errno){
	echo "connection to db failed"; 
	exit (); }
	// REGISTER USER
	if (isset($_GET['delete_db'])) {
		$id = mysqli_real_escape_string($db, $_GET['deleteid']);
	}
if ($id != "")
{
	$query = "DELETE FROM nanodb WHERE ID='". $id . "'";
	$create_table = "DELETE FROM nanodb WHERE id='". $id . "'";
	$dblog = mysqli_query($db,$create_table);


	header("Location:main.php");
	exit;
}
?>

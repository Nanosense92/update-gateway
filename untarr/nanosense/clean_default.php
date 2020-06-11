<?php
/*
 * script cleaning impact value without assigned place
 * */
$db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');// connect to the database
if($db->connect_errno){
    echo 'connection to db failed'; 
    exit;
}

$delquery = "DELETE FROM impact WHERE location ='default'";
echo $delquery;
$delog = $db->query($delquery);
$db->close();

header('Location:main.php');
exit;
?>

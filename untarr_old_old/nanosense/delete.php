<?php
/*
 * script to delete line in nanodb which contain reference for http post
 * */
$id = '';

if (isset($_POST['db_id2'])){
    $db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');// connect to the db
    if($db->connect_errno){
        echo 'connection to db failed'; 
        exit;
    }
    
    $id = mysqli_real_escape_string($db, $_POST['db_id2']);
    var_dump($id);
    if ($id != ''){
        $delquery = "DELETE FROM nanodb WHERE id = '$id'";
        $dblog = mysqli_query($db, $delquery);
        $db->close();
    }
    $db->close();
    header('Location:main.php');
    exit;
}
?>

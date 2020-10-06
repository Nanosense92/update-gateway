<?php
if (isset($_POST['reg_db'])){
    $db = mysqli_connect('localhost', 'jeedom', '85522aa27894d77', 'jeedom');
    if ($db->connect_errno){
        echo 'connection to db failed';
        exit;
    }

    // receive all input values from the form
    $login = mysqli_real_escape_string($db, $_POST['log']);
    $pass = mysqli_real_escape_string($db, $_POST['psw']);
    $addr = mysqli_real_escape_string($db, $_POST['addr']);
    $port = mysqli_real_escape_string($db, $_POST['port']);
    $path = mysqli_real_escape_string($db, $_POST['path']);
    $loc = mysqli_real_escape_string($db, $_POST['key']);

    if ($addr != '' && $path != '' && $port != ''){
        $modify_table = "UPDATE nanodb SET login = '$login', password = '$pass', addr = '$addr', port = '$port', path = '$path', location = '$loc' WHERE id = '$db_id'";
        $dbres = $db->query($modify_table);
        $db->close();
        header('Location:main.php');
        exit;
    }
    $db->close();
}
?>

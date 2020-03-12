<?php



function load_db() {
    $dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
    if ($dbconnect->connect_errno) {
        printf("Connection to 'jeedom' database failed");
        exit;
    }
    return $dbconnect;
}

function close_db($dbconnect) {
    mysqli_close($dbconnect);  	
}


function delete_device_from_eqLogic($alias)
{
    $dbconnect = load_db();
    $dbconnect->query("DELETE FROM eqLogic WHERE name=\"$alias\"");	
    echo $dbconnect->error;
    close_db($dbconnect);
}

echo $_GET['alias'];
delete_device_from_eqLogic($_GET['alias']);

?>

<script>
    window.location.replace("main.php");
</script>



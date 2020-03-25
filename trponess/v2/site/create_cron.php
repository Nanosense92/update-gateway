<?php

//##############################################CHECK############################

$error = 0;
if (isset($_GET['every']) === 'FALSE' || empty($_GET['every'])) {
    $error++;
    echo "toutes les heures/minutes n'ont pas ete specifiees<br>";
}
if (isset($_GET['nb']) === 'FALSE' || empty($_GET['nb'])) {
    $error++;
    echo "nb vide<br>";
}

if ($error > 0)
    exit(5334);

if ($_GET['every'] != 'heures' && $_GET['every'] != 'minutes') {
    $error++;
    echo "heures minutes mal ecrites<br>";
}



if ($error > 0)
    exit(5334);

if ($_GET['every'] === 'heures') {

    if ($_GET['nb'] < 1) 
    {   
        $error++;
        echo "heures moins 0<br>";
    }
}

if ($_GET['every'] === 'minutes') {
 
    if ($_GET['nb'] < 1) 
    {
        $error++;
        echo "mins moins 0<br>";
    }
}

if ($error > 0)
    exit(5334);

    
    









//##############################################CHECK############################
echo "ADDED TO CRONTAB";


$every = $_GET['every'];
$n = $_GET['nb'];

if ($_GET['every'] == 'heures') {

    exec("sudo python3 ../modbus_py/create_crontab.py '* */$n * * *'", $output, $return_value);
}
if ($_GET['every'] == 'minutes'){
    
    exec("sudo python3 ../modbus_py/create_crontab.py '*/$n * * * *'", $output, $return_value);
 
}

echo "<script> window,alert(\"SAVED going back to main page\"); </script>";
echo "<script> document.location.href='main2.php'; </script>";

?>
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
    if ($_GET['nb'] > 24) 
    {
        $error++;
        echo "heures depasse 24<br>";
    }

    if ($_GET['nb'] < 1) 
    {   
        $error++;
        echo "heures moins 0<br>";
    }
}

if ($_GET['every'] === 'minutes') {
    if ($_GET['nb'] > 60) 
    {
        $error++;
        echo "minutes depasse 60<br>";
    }
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

    
    exec("sudo python3 create_crontab.py '* */$n * * *'", $output, $return_value);
    //exec("echo '* */$n * * * sudo /usr/bin/python3.5 modbus_py/main.py 1' > /etc/crontab", $output, $return_value);
}
if ($_GET['every'] == 'minutes'){

    echo "x";
    exec("sudo python3 create_crontab.py '*/$n * * * *'", $output, $return_value);
    var_dump($output);
    //exec("echo '*/$n * * * * sudo /usr/bin/python3.5 modbus_py/main.py 1' > /etc/crontab", $output, $return_value);
}



?>
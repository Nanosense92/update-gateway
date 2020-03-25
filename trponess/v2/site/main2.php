
<!DOCTYPE HTML>  
<html>
<head>
</head>
<body style="background-color:#333333;">  
<link rel="stylesheet" href="css/main.css">


<script>
    function spawn_button(name) 
    {
        var button = document.createElement("button");
        button.innerHTML = name;

        var body = document.getElementsByTagName("body")[0];
        body.appendChild(button);

        button.addEventListener ("click", 
            function() {
                /*window.open('formulaire.php?device_chosen=' + name, '_blank'); */
                name = name.split(' ');
                name = name[0];
                document.location.href = 'form2.php?device_chosen=' + name;
            }
        );


    }
</script>

<button type=button onclick="document.location.href='form2.php?device_chosen='"> add device </button> 
<button type=button onclick="document.location.href='form2.php?device_chosen='"> log </button> 
<button type=button onclick="document.location.href='crontab.php'"> crontab </button> 

<?php

$names = array();

function get_session_names() {
    
    $names = array();
    $ret_parse_ini = parse_ini_file("../modbus_py/modbus__cache/session.ini", true);
    if ($ret_parse_ini === false) { echo "parse_ini_file() failed";exit(2);}
    #var_dump($ret_parse_ini);
    foreach ($ret_parse_ini as $k => $v) {
        $dict = $v;
        $section = $k;

        $z = array();

        if (array_key_exists("usb", $v) && array_key_exists("slaveid", $v))
        {
            array_push($z, (string)$k);
            array_push($z, (string)$v['usb']);//usb
        }
        array_push($z, (string)$v['slaveid']);//slaveid

        array_push($names, $z);//slaveid
        #echo $k . " " . var_dump($v) . "<br>";thunde 
    }
    return $names;
}

$names = get_session_names();


foreach ($names as $name) {
    
    if (count($name) == 3) 
        echo "<script> spawn_button(\"$name[0] (usb:$name[1] id:$name[2])\");</script>";
    else 
        echo "<script> spawn_button(\"$name[0]\");</script>";
}


//create session file
//load fron sessin file

//$x = "window.location.href=''"
//echo "<button class=button_scan onclick='window.location.href=$new_device_link'>add device</button>";
//echo "<button class=button_scan onclick=alert_and_launch_scan(\"$nb_scans\")>log</button> ";
//echo "<button class=button_scan onclick=alert_and_launch_scan(\"$nb_scans\")>crontab</button> ";
?>




</body>
</html>
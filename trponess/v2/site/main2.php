
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
                document.location.href = 'form2.php?device_chosen=' + name;
            }
        );


    }
</script>

<button type=button onclick="document.location.href='form2.php?device_chosen='"> add device </button> 

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
        array_push($names, (string)$k);
        #echo $k . " " . var_dump($v) . "<br>"; 
    }
    return $names;
}

$names = get_session_names();


foreach ($names as $name) {
    echo "<script> spawn_button(\"$name\");</script>";
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
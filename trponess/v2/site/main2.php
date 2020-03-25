
<!DOCTYPE HTML>  
<html>
<head>
<style>
	.error {
		color: #FF0000;
	}

	.field_to_fill {
		color: #DCDCDC;
		font-weight: bold;
		height: 150;
		padding: 8px;
		border: none;
		border-bottom: 1px solid #ccc;
		/* width: 10%; */
		size: 10;
	} 

	.button_back_to_main {
		color: white;
		border-radius: 4px;
		text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
		background: grey;
		font-size: 100%;
	}

	.button_destroy {
		color: white;
		border-radius: 4px;
		text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
		background: red;
		font-size: 100%;
	}

	h2 {
		color: grey;
		text-decoration: underline overline;
	}
</style>
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

<?php

    exec("sudo python3 ../modbus_py/get_crontab_info.py", $output, $return_value);
    if ($output !== "") {
        foreach ($output as $k => $v) {
            echo $v . "<br>";
            
        }
    }

?>

<button style="background : purple;" type=button onclick="document.location.href='user_log.php'"> log </button> 
<button style="background : blue;" type=button onclick="document.location.href='crontab.php'"> crontab </button> 
<br><br>
<button  style="background : green;" type=button onclick="document.location.href='form2.php?device_chosen='"> add device </button> 
<br><br>

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
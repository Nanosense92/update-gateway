
<!DOCTYPE HTML>  
<html>
<head>
</head>
<body style="background-color:#333333;">  
<link rel="stylesheet" href="css/main.css">



<button class=button_scan onclick="redirect_new_device()">add device</button>

<?php

//create session file
//load fron sessin file

//$x = "window.location.href=''"
//echo "<button class=button_scan onclick='window.location.href=$new_device_link'>add device</button>";
//echo "<button class=button_scan onclick=alert_and_launch_scan(\"$nb_scans\")>log</button> ";
//echo "<button class=button_scan onclick=alert_and_launch_scan(\"$nb_scans\")>crontab</button> ";
?>

<script>

function redirect_new_device() {
    document.location.href = "form2.php?device_chosen=456";
}


</script>


</body>
</html>
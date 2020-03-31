
<!DOCTYPE HTML>  
<html>
<head>
<style>
</style>
</head>

<?php
#load scan_config.php

if ($_GET['fill'] === "yes") {

    $inis = parse_ini_file("../modbus_py/modbus__cache/scan_config.ini", true);

    foreach ($inis as $x => $ini) {
        $usb = $ini['usb'];
        $baudrate = $ini['baudrate'];
        $stopbits = $ini['stopbits'];
        $bytesize = $ini['bytesize'];
        $slaveid =  $ini['slaveid'];
        $mode = $ini['mode'];
        $timeout = $ini['timeout'];
        $parity = $ini['parity'];
        break;
    }

}


?>

<form id="main_form" method="post" action="./scan_config_valid.php">  
    <!-- usb:  -->
    <span class="field_to_fill">usb</span>
	<input class =field_to_fill style=color:black; size="5" type="text" name="usb" value="<?php echo $usb;?>">
    
    
    <span class="field_to_fill">ids</span>
	<input class =field_to_fill style=color:black; size="10" type="text" name="usb" value="<?php echo $slaveid;?>">
    
    <span class="field_to_fill">baudrate</span>
	<input class =field_to_fill style=color:black; size="5" type="text" name="baudrate" value="<?php echo $baudrate;?>">
    
    
    <span class="field_to_fill">parity</span>
	<input class =field_to_fill style=color:black; size="5" type="text" name="parity" value="<?php echo $parity;?>">
	

    <span class="field_to_fill">bytesize</span>
	<input class =field_to_fill style=color:black; size="5" type="text" name="bytesize" value="<?php echo $bytesize;?>">
    
    
    <span class="field_to_fill">timeout</span>
	<input class =field_to_fill style=color:black; size="5" type="text" name="timeout" value="<?php echo $timeout;?>">
	
    <span class="field_to_fill">mode</span>
	<input class =field_to_fill style=color:black; size="5" type="text" name="mode" value="<?php echo $mode;?>">
	
    <span class="field_to_fill">stopbits</span>
	<input class =field_to_fill style=color:black; size="5" type="text" name="stopbits" value="<?php echo $stopbits;?>">
	
	<input type="hidden" name="device_chosen" value=<?php echo $device_chosen;?> >

	<!-- VALIDER -->
	<input class=button_valider type="submit" value="Valider" > 
</form>


<br><br><br> <button style=background:grey;position:absolute;top:500px;left:500px; onclick="document.location.href='scan_config.php?fill=no';">clear</button>

</body>
</html>
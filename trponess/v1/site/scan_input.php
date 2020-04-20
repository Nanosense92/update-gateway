<body style="background-color:#333333;">

<p style='color:white;'>Interroge les sondes connectées via modbus</p>
<p style='color:white;'>les adresses vont de 1 - 254</p>

<p style='color:white;'>24,7,1 = scan id24 et id7 et id1</p>
<p style='color:white;'>50-100 = scan les ids 50 à 100</p> 
<p style='color:white;'>all pour id 1-254</p>

<p style='color:white;'>exemple: 24,7 40-80 22-1 0,4,9</p>



<form id="main_form" method="post" action="./scan_launch.php">  
    <!-- usb:  -->
    <span class="field_to_fill"></span>
	<input class =field_to_fill style=color:black; type="text" name="scan">
	<!-- VALIDER -->
	<input class=button_valider type="submit" name="submit" value="Valider"> 
</form>

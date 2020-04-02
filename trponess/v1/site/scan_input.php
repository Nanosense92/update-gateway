<p>Interroge les sondes connectées via usb mode rtu </p>
<p>24,7,1 = scan id24 et id7 et id1</p>
<p>50-100 = scan les ids 50 à 100</p> 
<p>les adresses vont de 1 - 254</p>
<p>exemple: 24,7 40-80 22-1 0,4,9</p>
<p>all pour id 1-254</p>


<form id="main_form" method="post" action="./scan_launch.php">  
    <!-- usb:  -->
    <span class="field_to_fill">scan</span>
	<input class =field_to_fill style=color:black; type="text" name="scan">
	<!-- VALIDER -->
	<input class=button_valider type="submit" name="submit" value="Valider"> 
</form>

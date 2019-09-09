<?php include('getphysio.php') ?>
<?php include('getdata.php') ?>
<!DOCTYPE html>
<HTML>
	<HEAD>
		<style>
button {
 background-color: #008CBA;
border: none;
color: white;
padding: 10px 15px;
text-align: center;
text-decoration: none;
display: inline-block;
font-size: 15px;
}
.selection {
 background-color: #008CBA;
border: solid;
color: white;
padding: 10px 12px;
text-align: center;
text-decoration: none;
display: inline-block;
font-size: 15px;
}
.raw {
	display: flex;
}
.topleft {
	position: absolute;
	top: 0px;
	left: 0px;
}
.padding{
	padding-top:50px;
	margin: 100px;
}

		</style>
		<!--Load the AJAX API-->
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">
// Load the Visualization API.
google.load('visualization', '1', {'packages':['corechart', 'controls']});
// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart);
function drawChart() {
	var jsontable = <?php echo json_encode($table) ?>;
	var str = 'graphique effet physiologique: ';
	var name = str.concat('<?php echo $name_table?>');
	var data = new google.visualization.DataTable(jsontable);

	var chart = new google.visualization.ChartWrapper({
	chartType: 'LineChart',
		containerId: 'chart_div',
		options: {
		title: name,
			height: 400,
			// omit width, since we set this in CSS
			chartArea: {
			width: '75%' // this should be the same as the ChartRangeFilter
	},
		explorer:{
		actions: ['dragToZoom', 'rightClickToReset'],
			axis: 'default',
			keepInBounds: true,
			maxZoomIn: 1000.0}
	}
	});

	var control = new google.visualization.ControlWrapper({
	controlType: 'ChartRangeFilter',
		containerId: 'control_div',
		options: {
		filterColumnIndex: 0,
			ui: {
			chartOptions: {
			height: 50,
				// omit width, since we set this in CSS
				chartArea: {
				width: '75%' // this should be the same as the ChartRangeFilter
	}
	}
	}
	}
	});

	var dashboard = new google.visualization.Dashboard(document.querySelector('#dashboard_div'));
	dashboard.bind([control], [chart]);
	dashboard.draw(data);
	function All_data () {
		var range = data.getColumnRange(0);
		control.setState({
		range: {
		start: range.min,
			end: range.max
		}
		});
		control.draw();
	}
	function zoomLasthour () {
		var range = data.getColumnRange(0);
		control.setState({
		range: {
		start: new Date(range.max.getFullYear(), range.max.getMonth(), range.max.getDate(), range.max.getHours() - 1),
			end: range.max
		}
		});
		control.draw();
	}
	function zoomLastDay () {
		var range = data.getColumnRange(0);
		control.setState({
		range: {
		start: new Date(range.max.getFullYear(), range.max.getMonth(), range.max.getDate() - 1),
			end: range.max
		}
		});
		control.draw();
	}
	function zoomLastWeek () {
		var range = data.getColumnRange(0);
		control.setState({
		range: {
		start: new Date(range.max.getFullYear(), range.max.getMonth(), range.max.getDate() - 7),
			end: range.max
		}        
		});
		control.draw();
	}
	function zoomLastMonth () {
		// zoom here sets the month back 1, which can have odd effects when the last month has more days than the previous month
		// eg: if the last day is March 31, then zooming last month will give a range of March 3 - March 31, as this sets the start date to February 31, which doesn't exist
		// you can tweak this to make it function differently if you want
		var range = data.getColumnRange(0);
		control.setState({
		range: {
		start: new Date(range.max.getFullYear(), range.max.getMonth() - 1, range.max.getDate()),
			end: range.max
		}
		});
		control.draw();
	}

	var runOnce = google.visualization.events.addListener(dashboard, 'ready', function () {
		google.visualization.events.removeListener(runOnce);

		if (document.addEventListener) {
			document.querySelector('#All_data').addEventListener('click', All_data);
			document.querySelector('#lasthour').addEventListener('click', zoomLasthour);
			document.querySelector('#lastDay').addEventListener('click', zoomLastDay);
			document.querySelector('#lastWeek').addEventListener('click', zoomLastWeek);
			document.querySelector('#lastMonth').addEventListener('click', zoomLastMonth);
		}
		else if (document.attachEvent) {
			document.querySelector('#All_data').attachEvent('onclick', All_data);
			document.querySelector('#lasthour').attachEvent('onclick', zoomLasthour);
			document.querySelector('#lastDay').attachEvent('onclick', zoomLastDay);
			document.querySelector('#lastWeek').attachEvent('onclick', zoomLastWeek);
			document.querySelector('#lastMonth').attachEvent('onclick', zoomLastMonth);
		}
		else {
			document.querySelector('#All_data').onclick = All_data;
			document.querySelector('#lasthour').onclick = zoomLasthour;
			document.querySelector('#lastDay').onclick = zoomLastDay;
			document.querySelector('#lastWeek').onclick = zoomLastWeek;
			document.querySelector('#lastMonth').onclick = zoomLastMonth;
		}
	});

	var column_manager = [0,1,2,3,4,5];
	var reset = document.getElementById("resettest");
	reset.onclick = function()
	{
		column_manager = [0,1,2,3,4,5];
		view = new google.visualization.DataView(data);
		dashboard.draw(view);
	}
	var showP = document.getElementById("showP");
	showP.onclick = function()
	{
		var trig = 0;
		for( var i = 0; i < column_manager.length; i++){ 
			if ( column_manager[i] === 1) {
				trig = 1;

				if (column_manager.length > 2)
					column_manager.splice(i, 1); 
			}
		}
		if (trig === 0) {
			if (column_manager.length >= 2)
				column_manager.splice(1,0,1);
			else
				column_manager.push(1);
		}
		view = new google.visualization.DataView(data);
		view.setColumns(column_manager);
		dashboard.draw(view);
	}
	var showH = document.getElementById("showH");
	showH.onclick = function()
	{
		var trig = 0;
		for( var i = 0; i < column_manager.length; i++){ 
			if ( column_manager[i] === 2) {
				trig = 1;
				if (column_manager.length > 2)
					column_manager.splice(i, 1); 
			}
		}
		if (trig === 0) {
			if (column_manager.length >= 3)
				column_manager.splice(2,0,2);
			else
				column_manager.push(2);
		}
		view = new google.visualization.DataView(data);
		view.setColumns(column_manager);
		dashboard.draw(view);
	}
	var showS = document.getElementById("showS");
	showS.onclick = function()
	{
		var trig = 0;
		for( var i = 0; i < column_manager.length; i++){ 
			if ( column_manager[i] === 3) {
				trig = 1;
				if (column_manager.length > 2)
					column_manager.splice(i, 1); 
			}
		}
		if (trig === 0) {
			if (column_manager.length >= 4)
				column_manager.splice(3,0,3);
			else
				column_manager.push(3);
		}
		view = new google.visualization.DataView(data);
		view.setColumns(column_manager);
		dashboard.draw(view);
	}
	var showI = document.getElementById("showI");
	showI.onclick = function()
	{
		var trig = 0;
		for( var i = 0; i < column_manager.length; i++){ 
			if ( column_manager[i] === 4) {
				trig = 1;
				if (column_manager.length > 2) 
					column_manager.splice(i, 1); 
			}
		}
		if (trig === 0) {
			if (column_manager.length >= 5)
				column_manager.splice(4,0,4);
			else
				column_manager.push(4);
		}

		view = new google.visualization.DataView(data);
		view.setColumns(column_manager);
		dashboard.draw(view);
	}
	var showN = document.getElementById("showN");
	showN.onclick = function()
	{
		var trig = 0;
		for( var i = 0; i < column_manager.length; i++){ 
			if ( column_manager[i] === 5) {
				trig = 1;
				if (column_manager.length > 2) 
					column_manager.splice(i, 1); 
			}
		}
		if (trig === 0) {
			if (column_manager.length >= 6)
				column_manager.splice(5,0,5);
			else
				column_manager.push(5);
		}
		view = new google.visualization.DataView(data);
		view.setColumns(column_manager);
		dashboard.draw(view);

	}
	var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(data));
	var dlAnchorElem = document.getElementById('downloadAnchorElem');
	dlAnchorElem.setAttribute("href",     dataStr     );
	dlAnchorElem.setAttribute("download", name+"-table.json");
}
//test
$(document).ready(function() {
	$(".btn").click(function() {
		google.load("visualization", "1", {packages:["corechart"], callback: drawChart});
		function drawChart() {

			var jdata = <?php echo json_encode($datatable)?>;

			var options = {
			height: 400,
				chartArea: {
				width: '75%' 
			},
				explorer:{
				actions: ['dragToZoom', 'rightClickToReset'],
					axis: 'default',
					keepInBounds: true,
					maxZoomIn: 1000.0}

			};
			var jsondata = new google.visualization.DataTable(jdata);
			var chart = new google.visualization.LineChart(document.getElementById('piechart'));

			chart.draw(jsondata, options);

			var column_manager = [0,1,2,3,4,5,6,7];
			var reset = document.getElementById("reset");
			reset.onclick = function()
			{
				column_manager = [0,1,2,3,4,5,6,7];
				view = new google.visualization.DataView(jsondata);
				chart.draw(view, options);
			}
			var showP = document.getElementById("showProd");
			showP.onclick = function()
			{
				column_manager = [0,1,3,4,5,6,7];
				view = new google.visualization.DataView(jsondata);
				view.setColumns(column_manager);
				chart.draw(view, options);
			}
			var showH = document.getElementById("showHealth");
			showH.onclick = function()
			{
				column_manager = [0,1,4,5,6,7];
				view = new google.visualization.DataView(jsondata);
				view.setColumns(column_manager);
				chart.draw(view, options);
			}
			var showS = document.getElementById("showSleep");
			showS.onclick = function()
			{
				column_manager = [0,1,3,4,5,6,7];
				view = new google.visualization.DataView(jsondata);
				view.setColumns(column_manager);
				chart.draw(view, options);
			}
			var showI = document.getElementById("showIrritation");
			showI.onclick = function()
			{
				column_manager = [0,2,4,5,6];
				view = new google.visualization.DataView(jsondata);
				view.setColumns(column_manager);
				chart.draw(view , options);
			}
			var showN = document.getElementById("showNoise");
			showN.onclick = function()
			{
				column_manager = [0];
				view = new google.visualization.DataView(jsondata);
				view.setColumns(column_manager);
				chart.draw(view, options);

			}
		}
	});
});
//end test
</script>
		<TITLE>Nanosense Gateway</TITLE>
		<div class "raw">
			<div class="topleft">
				<a href="main.php"><IMG SRC="logo-nano.png"></a>
			</div>
			<CENTER><a href="graphysio.php"><IMG SRC="nano-header.png" width=322" height=" 63"><a></CENTER>
		</div> 
	</HEAD>
	<BODY>
		<div class= padding>
<form method="post" action="graphysio.php">
<select name="select_loc" class="selection">
<?php
$conn = mysqli_connect('localhost','jeedom','85522aa27894d77','jeedom');
$res = $conn->query("SELECT DISTINCT location from impact");
if ($res->num_rows >= 0){
	while($row = $res->fetch_array(MYSQLI_BOTH)){
		$option = "<option value=" .$row[0] . ">" . $row[0] . "</option>";
		echo $option;
	}
}
$conn->close();
?>
</select>
<button type="submit" class="button" name="submit" value="Get selected value">post</button>
</form>
	<div id="dashboard_div">
			<button type="button" class="button" id="lasthour">Last Hour</button>
			<button type="button" class="button" id="lastDay">Last Day</button>
			<button type="button" class="button" id="lastWeek">Last Week</button>
			<button type="button" class="button" id="lastMonth">last Month</button>
			<button type="button" class="button" id="All_data">All</button>
			</br>
					<center><div id="chart_div" style='width= 800px; height: 400px;'></div></center>
		<center><div id="control_div" style='width= 800px; height: 100px;'></div></center>
			<button type="button" class="button" id="resettest">reset</button>
			<button type="button" class="button" id="showP">Productivity</button>
			<button type="button" class="button" id="showH">Health</button>
			<button type="button" class="button" id="showS">Sleep quality</button>
			<button type="button" class="button" id="showI">Irritation</button>
			<button type="button" class="button" id="showN">Noise Confort</button>
		<div align="right"><button class="button"><a id="downloadAnchorElem">Download-Json</a></button></div>
</div>
<div id="button"><button class="btn">Plot chart!</button></div>
<div id="piechart" class="piechart"></div>
			<button type="button" class="button" id="reset">reset</button>
			<button type="button" class="button" id="showProd">Productivity</button>
			<button type="button" class="button" id="showHealth">Health</button>
			<button type="button" class="button" id="showSleep">Sleep quality</button>
			<button type="button" class="button" id="showIrritation">Irritation</button>
			<button type="button" class="button" id="showNoise">Noise Confort</button>

	</BODY>
</HTML>


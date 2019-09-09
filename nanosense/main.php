
<?php include('delete.php') ?>
<!DOCTYPE html>
<html>
	<style>
.raw {
	display: flex;
}
.topleft {
	position: absolute;
	top: 0px;
	left: 0px;
}
.padding{
	padding-top:100px;
	margin: 80px;
}
body{
	font-family: 'Source code pro', arial, serif;
	margin: 0px;
	padding: 0px;
}
nav > ul{
	margin: 0px;
	padding: 0px;
}
nav > ul > li {
	float: left;
}
nav li{
	list-style-type: none;
}
.submenu{
	display: none;
}
nav{
	width: 100%;
	background-color: #424558;
}
nav > ul > li{
	float:left;
	position: relative;
}
nav > ul::after{
	content: "";
	display: block;
	clear: both;
}
nav a{
	display: inline-block;
	text-decoration: none;
}
nav > ul > li > a{
	padding: 20px 30px;
	color: #FFF;
}
nav li:hover .submenu{
	display: inline-block;
	position: absolute;
	top: 100%;
	left: 0px;
	padding: 0px;
	z-index: 100000;
}
.submenu li{
	border-bottom: 1px solid #FFF;
}
.submenu li a{
	padding 15px 30px;
font-size: 13px;
color: #222538;
width: 270px;
}
.menu-1:hover{
	border-top: 5px solid #6EAAF0;
	background-color: RGBa(0,0, 0, 0.15);
}
nav > ul > li:hover a{
	padding: 15px 30px 20px 30px;
}
.menu-1 .submenu{
	background-color: RGB(110, 170, 240);
}
.submenu li:hover a{
	color:#EEE;
	font-weight: bold;
}
.menu-1 .submenu li:hover{
	background-color: RGB(50, 100, 150);
}
	</style>
	<head>
 <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
 <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">
google.charts.load('current', {'packages':['table']});
google.charts.setOnLoadCallback(drawTable);

function drawTable() {
	var jsonData = $.ajax({
	url: "shownanodb.php",
		dataType: "json",
		async: false
}).responseText;
var data = new google.visualization.DataTable(jsonData);

var table = new google.visualization.Table(document.getElementById('table_div'));

table.draw(data, {showRowNumber: true, width: '100%', height: '100%'});
}
</script>

		<meta charset= "utf-8">
	<div class "raw">
<CENTER><a href="main.php"><IMG SRC="nano-header.png" width="322" height="63"></a></CENTER>
</div>
	</head>
	<body>
		<nav>
			<ul>
				<li class="menu-1"><a href="#">Database</a>
					<ul class="submenu">
						<li><a href="register.php">Add new Database</a></li>
						<li><a href="showlog.php">Last Post</a></li>
						<li><a href="../index.php?v=d&p=dashboard">Back to jeedom</a></li>
					</ul>
				</li>
				<li class="menu-1"><a href="#">Physiological effect</a>
					<ul class="submenu">
						<li><a href="graphysio.php">Visualization</a></li>
						<li><a href="clean.php">Clean</a></li>
						<li><a href="clean_default.php">Clean default</a></li>
					</ul>
				</li>
<li class="menu-1"><a href="#">Setting</a>
					<ul class="submenu">
						<li><a href="update.php">Update</a></li>
						<li><a href="updatelog.php">Update Log</a></li>
					</ul>
				</li>

			</ul>
		</nav>
		<div id="table_div"></div>
		<form action="delete.php" method="get">
			ID : <input type="number" name="deleteid" required>
			<button type="submit" name="delete_db">delete</button>
		</form>
	</body>
</html>

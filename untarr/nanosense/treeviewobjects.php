<?php
/*
 * script that display the tree view of the different objects
 * */

require_once "/home/pi/enocean-gateway/get_database_password.php";

$db = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');
if ($db->connect_errno){
    printf('connection failed to db');
    exit;
}

$query = "SELECT o.id, o.name AS name, f.name AS father_name FROM object o LEFT JOIN object f ON o.father_id = f.id";
$res = $db->query($query);
$array = $res->fetch_all(MYSQLI_NUM);

?>
<!DOCTYPE HTML>
<html>
    <head>
        <style>
        .topleft {
            position: absolute;
            top: 0px;
            left: 0px;
        }
        </style>

        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
        google.charts.load('current', {packages:["orgchart"], callback: drawChart});
        
        function drawChart(){
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Father name');
            var array_data = <?php echo json_encode($array)?>;
            array_data.forEach(addData);

            function addData(node){
                data.addRows([[node[1], node[2]]]);
            }

            var options = {
                title: 'Tree view : objects created in Jeedom',
                    
                allowCollapse: true,
                allowHtml: true,
                size: 'small',
            };

            var chart = new google.visualization.OrgChart(document.getElementById('objects_treeview_div'));
            chart.draw(data, options);
        }
        </script>

        <title>Tree view : objects created in Jeedom</title>
        <div style="padding-bottom: 50px">
            <div class="topleft">
                <a href="main.php">
                    <img src="logo-nano.png">
                </a>
            </div>
            <center>
                <a href="treeviewobjects.php">
                    <img src="nano-header.png" width="322" height="63">
                </a>
            </center> 
        </div>
    </head>
    <body>
        <div id="objects_treeview_div"></div>
    </body>
</html>

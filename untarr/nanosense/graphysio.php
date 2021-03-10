<?php include('getphysio.php') ?>
<?php include('getdata.php') ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Physiological effects</title>
        <link rel="stylesheet" type="text/css" href="graphs.css">
        <meta charset="UTF-8">
        <div>
            <div class="topleft">
                <a href="main.php">
                    <img src="logo-nano.png">
                </a>
            </div>
            <center>
                <a href="graphysio.php">
                    <img src="nano-header.png" width="322" height="63">
                </a>
            </center>
        </div>

        <!--Load the AJAX API-->
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script type="text/javascript">

        // Load the Visualization API with the callback to run when it is loaded
        google.load('visualization', '1', {packages:['corechart', 'controls'], callback: drawChart});

        // the first chart: physiological effects        
        function drawChart(){
            var jsontable = <?php echo json_encode($table) ?>;
            var name = 'Physiological effects';
            var submit_location = '<?php echo $name_table; ?>';
            if (submit_location != 'None'){
                name = name.concat(': <?php echo $name_table?>', ' between ', '<?php echo $start_date?>', ' and ', '<?php echo $end_date?>');
            }

            var data = new google.visualization.DataTable(jsontable);
            var chart = new google.visualization.ChartWrapper({
                chartType: 'LineChart',
                containerId: 'chart_div',
                options: {
                    title: name,
                    height: 400,
                    width: 1500,
                    chartArea: {
                        width: '75%' // this should be the same as the ChartRangeFilter
                    },
                    explorer:{
                        actions: ['dragToZoom', 'rightClickToReset'],
                        axis: 'default',
                        keepInBounds: true,
                        maxZoomIn: 1000.0
                    },
                    hAxis:{
                        title: 'Datetime'
                    },
                    vAxis:{
                        title: 'Effects'
                    }
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
                            width: 1500,
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

            function All_data(){
                var range = data.getColumnRange(0);
                control.setState({
                    range: {
                        start: range.min,
                        end: range.max
                    }
                });
                control.draw();
            }

            function zoomLasthour(){
                var range = data.getColumnRange(0);
                control.setState({
                    range: {
                        start: new Date(range.max.getFullYear(), range.max.getMonth(), range.max.getDate(), range.max.getHours() - 1),
                        end: range.max
                    }
                });
                control.draw();
            }

            function zoomLastDay(){
                var range = data.getColumnRange(0);
                control.setState({
                    range: {
                        start: new Date(range.max.getFullYear(), range.max.getMonth(), range.max.getDate() - 1),
                        end: range.max
                    }
                });
                control.draw();
            }

            function zoomLastWeek(){
                var range = data.getColumnRange(0);
                control.setState({
                    range: {
                        start: new Date(range.max.getFullYear(), range.max.getMonth(), range.max.getDate() - 7),
                        end: range.max
                    }        
                });
                control.draw();
            }

            function zoomLastMonth(){
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
                if (document.addEventListener){
                    document.querySelector('#All_data').addEventListener('click', All_data);
                    document.querySelector('#lasthour').addEventListener('click', zoomLasthour);
                    document.querySelector('#lastDay').addEventListener('click', zoomLastDay);
                    document.querySelector('#lastWeek').addEventListener('click', zoomLastWeek);
                    document.querySelector('#lastMonth').addEventListener('click', zoomLastMonth);
                }
                else if (document.attachEvent){
                    document.querySelector('#All_data').attachEvent('onclick', All_data);
                    document.querySelector('#lasthour').attachEvent('onclick', zoomLasthour);
                    document.querySelector('#lastDay').attachEvent('onclick', zoomLastDay);
                    document.querySelector('#lastWeek').attachEvent('onclick', zoomLastWeek);
                    document.querySelector('#lastMonth').attachEvent('onclick', zoomLastMonth);
                }
                else{
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

        // the second chart: raw qaa data (Temperature, humidity, CO2, ...)
        google.load("visualization", "1", {packages:["corechart"], callback: drawChart2});
        function drawChart2(){
            var jsontable = <?php echo json_encode($datatable)?>;
            var name = 'Raw QAA data';
            var submit_location = '<?php echo $name_table; ?>';
            if (submit_location != 'None'){
                name = name.concat(': <?php echo $name_table?>', ' between ', '<?php echo $start_date?>', ' and ', '<?php echo $end_date?>');
            }

            var data = new google.visualization.DataTable(jsontable);
            var options = {
                title: name,
                height: 400,
                width: 1500,
                chartArea: {
                    width: '75%' 
                },
                explorer:{
                    actions: ['dragToZoom', 'rightClickToReset'],
                    axis: 'default',
                    keepInBounds: true,
                    maxZoomIn: 1000.0
                },
                hAxis:{
                    title: 'Datetime'
                },
                vAxis:{
                    title: 'Measured data'
                }
            };
            var chart = new google.visualization.LineChart(document.getElementById('chart2_div'));

            chart.draw(data, options);

            // 0 = Time, 1 = Temperature, 2 = Humidity, 3 = C02, 4 = PM2.5, 5 = PM10, 6 = PM1, 7 = VOC
            var column_manager = [0,1,2,3,4,5,6,7];

            var reset = document.getElementById("reset");
            reset.onclick = function()
            {
                column_manager = [0,1,2,3,4,5,6,7];
                view = new google.visualization.DataView(data);
                chart.draw(view, options);
            }

            var showP = document.getElementById("showProd");
            showP.onclick = function()
            {
                column_manager = [0,1,3,4,5,6,7];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);
            }

            var showH = document.getElementById("showHealth");
            showH.onclick = function()
            {
                column_manager = [0,1,4,5,6,7];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);
            }

            var showS = document.getElementById("showSleep");
            showS.onclick = function()
            {
                column_manager = [0,1,3,4,5,6,7];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);
            }

            var showI = document.getElementById("showIrritation");
            showI.onclick = function()
            {
                column_manager = [0,2,4,5,6];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view , options);
            }

            var showN = document.getElementById("showNoise");
            showN.onclick = function()
            {
                column_manager = [0];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);

            }

            var showTemperature = document.getElementById("showTemperature");
            showTemperature.onclick = function()
            {
                column_manager = [0,1];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);
            }

            var showHumidity = document.getElementById("showHumidity");
            showHumidity.onclick = function()
            {
                column_manager = [0,2];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);
            }

            var showCO2 = document.getElementById("showCO2");
            showCO2.onclick = function()
            {
                column_manager = [0,3];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);
            }

            var showPM2_5 = document.getElementById("showPM2.5");
            showPM2_5.onclick = function()
            {
                column_manager = [0,4];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);
            }

            var showPM10 = document.getElementById("showPM10");
            showPM10.onclick = function()
            {
                column_manager = [0,5];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);
            }

            var showPM1 = document.getElementById("showPM1");
            showPM1.onclick = function()
            {
                column_manager = [0,6];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);
            }

            var showVOC = document.getElementById("showVOC");
            showVOC.onclick = function()
            {
                column_manager = [0,7];
                view = new google.visualization.DataView(data);
                view.setColumns(column_manager);
                chart.draw(view, options);
            }
        }

        $(document).ready(function(){
            $("#select_loc").change(function(){
                document.getElementById("start_date").setAttribute("max", $(this).find(":selected").data('max'));
                document.getElementById("start_date").setAttribute("min", $(this).find(":selected").data('min'));
                document.getElementById("end_date").setAttribute("max", $(this).find(":selected").data('max'));
                document.getElementById("end_date").setAttribute("min", $(this).find(":selected").data('min'));
            });

            $("#start_date").change(function(){
                document.getElementById("end_date").setAttribute("min", document.getElementById("start_date").value);
            });
        });
        </script>
    </head>

    <body>
        <div class= padding>
        <form id="data_form" method="post" action="graphysio.php">
            <select id="select_loc" name="select_loc" class="selection">
            <?php
            require_once "/home/pi/enocean-gateway/get_database_password.php";
            $conn = mysqli_connect('localhost','jeedom',$jeedom_db_passwd,'jeedom');
            $res = $conn->query("SELECT location, date(min(datetime)) AS min_date, date(max(datetime)) AS max_date FROM impact GROUP BY location");
            $isFirst = true;
            $first_min = '';
            $first_max = '';
            if ($res->num_rows >= 0){
                while($row = $res->fetch_array(MYSQLI_BOTH)){
                    if ($isFirst == true){
                        $first_min = $row[1];
                        $first_max = $row[2];
                        $option = "<option value=\"$row[0]\" selected data-min=\"$row[1]\" data-max=\"$row[2]\">$row[0]</option>";
                        $isFirst = false;
                    }
                    else{
                        $option = "<option value=\"$row[0]\" data-min=\"$row[1]\" data-max=\"$row[2]\">$row[0]</option>";
                    }
                    echo $option;
                }
            }
            $conn->close();
            ?>
            </select>    
            <label for="start">Start date:</label>
            <input type="date" id="start_date" name="start_date" min="<?php echo $first_min?>" max="<?php echo $first_max?>" required>
            <label for="start">End date:</label>
            <input type="date" id="end_date" name="end_date" min="<?php echo $first_min?>" max="<?php echo $first_max?>" required>
            <button type="submit" class="red_button" name="submit" value="Get selected value">Post data</button>
        </form>
        <div id="dashboard_div">
            <button type="button" class="button" id="lasthour">Last hour</button>
            <button type="button" class="button" id="lastDay">Last day</button>
            <button type="button" class="button" id="lastWeek">Last week</button>
            <button type="button" class="button" id="lastMonth">Last month</button>
            <button type="button" class="button" id="All_data">All</button>
            <br />

            <div id="chart_div" style="width: 800px; height: 400px;"></div>
            <div id="control_div" style="width: 800px; height: 100px;"></div>

            <button type="button" class="button" id="resettest">Reset</button>
            <button type="button" class="button" id="showP">Productivity</button>
            <button type="button" class="button" id="showH">Health</button>
            <button type="button" class="button" id="showS">Sleep quality</button>
            <button type="button" class="button" id="showI">Irritation</button>
            <button type="button" class="button" id="showN">Noise comfort</button>
            <div align="right">
                <button class="button">
                    <a id="downloadAnchorElem">Download-Json</a>
                </button>
            </div>
        </div>

        <div id="rawQAAData_div">
            <div id="chart2_div" style="width: 800px; height: 400px;"></div>
            
            <button type="button" class="button" id="reset">Reset</button>
            <button type="button" class="button" id="showProd">Productivity</button>
            <button type="button" class="button" id="showHealth">Health</button>
            <button type="button" class="button" id="showSleep">Sleep quality</button>
            <button type="button" class="button" id="showIrritation">Irritation</button>
            <button type="button" class="button" id="showNoise">Noise comfort</button>
            <br />
            <br />

            <button type="button" class="button" id="showTemperature">Temperature</button>
            <button type="button" class="button" id="showHumidity">Humidity</button>
            <button type="button" class="button" id="showCO2">CO2</button>
            <button type="button" class="button" id="showPM1">PM1</button>
            <button type="button" class="button" id="showPM2.5">PM2.5</button>
            <button type="button" class="button" id="showPM10">PM10</button>
            <button type="button" class="button" id="showVOC">VOC</button>
        </div>
    </body>
</html>

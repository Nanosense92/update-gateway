<?php
$jsonfile = file_get_contents("/home/pi/Nano-Setting.json");
$jsondec = json_decode($jsonfile, true);
$offset = NULL;
foreach($jsondec AS $key => $value){
    if($key == "timezone"){
        $offset = (int)$value;
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title> Home </title>
        <link rel="stylesheet" type="text/css" href="main.css">
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script type="text/javascript">
        google.charts.load('current', {'packages':['table']});
        google.charts.setOnLoadCallback(drawTable); 
        
        function displayConfirm(value){
            var confirmModal = document.getElementById("confirm_modal");
            confirmModal.style.display = "block"; // display the confirmation popup (here a modal)
            var hiddenInputPost = document.getElementById("db_id2");
            hiddenInputPost.value = value;
        }

        function closeConfirm(){
            var confirmModal = document.getElementById("confirm_modal");
            confirmModal.style.display = "none"; // hide the confirmation popup
        }

        function displayFilename(){
            var filenameModal = document.getElementById("filename_modal");
            filenameModal.style.display = "block"; // display the popup that ask for a filename to save
        }

        function closeFilename(){
            var filenameModal = document.getElementById("filename_modal");
            filenameModal.style.display = "none"; // hide the popup with the filename
        }

        // function that "close" the modals by clicking outside
        window.onclick = function(event){
            var confirmModal = document.getElementById("confirm_modal");
            var filenameModal = document.getElementById("filename_modal");
            if (event.target == confirmModal){
                confirmModal.style.display = "none";
            }
            if (event.target == filenameModal){
                filenameModal.style.display = "none";
            }
        }

        // callback function to draw the nanodb sql table in the page
        function drawTable(){
            var jsonData = $.ajax({
                url: "shownanodb.php",
                dataType: "json",
                async: false
            }).responseText;
            var data = new google.visualization.DataTable(jsonData);
            var buttonColumnIndex = data.addColumn('string', '');
            for (var i = 0; i < data.getNumberOfRows(); i++){
                // add 2 buttons in the last column of the data table to modify and delete the line
                data.setCell(i, buttonColumnIndex, '<div class="button-container"><form action="modify.php" method="post"><div><input type="hidden" name="db_id" id="modify_' + i + '" value="'
                    + data.getValue(i, 0) + '"><button type="submit" name="submit_id">modify</button></div></form>' +
                    '<form method="get"><div><input type="hidden" name="delete_input" id="delete_' + i + '" value="'
                    + i + '"><button type="button" name="delete_db" onclick="displayConfirm(' + data.getValue(i, 0) + ')">delete</button></div></form></div>');
                for (var j = 0; j < data.getNumberOfColumns(); j++){
                    data.setProperty(i, j, 'style', 'text-align: center'); 
                }
            }
            var table = new google.visualization.Table(document.getElementById('table_div'));
            table.draw(data, {allowHtml: true, showRowNumber: true, width: '100%', height: '100%'});
        }

        // refresh the datetime every second
        function refresh_datetime(){
            var refresh = 1000;
            setTimeout('display_date("<?php echo gethostname(); ?>")', refresh);
        }

        // display the date on the page 
        function display_date(hostname){

            //return;

            var offset = <?php echo $offset ?>; // offset of the timezone in hours (utc+2 = +2 hours)
            var cur_datetime = new Date();
            cur_datetime = new Date(Date.UTC(cur_datetime.getUTCFullYear(), cur_datetime.getUTCMonth(), cur_datetime.getUTCDate(),
                cur_datetime.getUTCHours(), cur_datetime.getUTCMinutes(), cur_datetime.getUTCSeconds(), cur_datetime.getUTCMilliseconds()));
            cur_datetime.setTime(cur_datetime.getTime() + offset*60*60*1000);
            var UTCString = " UTC +";
            if (offset < 0){
                UTCString = " UTC ";
            }
            var cur_year = cur_datetime.getUTCFullYear();
            var cur_month = cur_datetime.getUTCMonth()+1; // month start with 0 (3 = april for example)

            // add a zero at the beginning of each number (month, day, hour, ...) if it's < 10
            if (cur_month < 10)
                cur_month = "0" + cur_month;
            var cur_day = cur_datetime.getUTCDate();
            if (cur_day < 10)
                cur_day = "0" + cur_day;
            var cur_hour = cur_datetime.getUTCHours();
            if (cur_hour < 10)
                cur_hour = "0" + cur_hour;
            var cur_minute = cur_datetime.getUTCMinutes();
            if (cur_minute < 10)
                cur_minute = "0" + cur_minute;
            var cur_second = cur_datetime.getUTCSeconds();
            if (cur_second < 10)
                cur_second = "0" + cur_second;

            
            // set the value of the html element with a good display
            
            document.getElementById('datetime').innerHTML = hostname;
            //document.getElementById('datetime').innerHTML = hostname + "<br>" + cur_year + "-" + cur_month + "-" + cur_day + " " + cur_hour + ":" + cur_minute + ":" + cur_second + UTCString + offset;
            //refresh_datetime();
        }

        </script>
        <meta charset="utf-8">
        <div>
            <center>
                <a href="main.php">
                    <img src="nano-header.png" width="322" height="63">
                </a>
            </center>
            <div align="right"><span id='datetime'></span></div> 
        </div>
    </head>
    <body onload="display_date('<?php echo gethostname(); ?>');">
        <nav>
            <ul>
                <li class="menu-1"><a href="#">Database</a>
                    <ul class="submenu">
                        <li><a href="register.php">Add new Database</a></li>
                        <li><a href="showlog.php">Last Post</a></li>
                    </ul>
                </li>
                <li class="menu-1"><a href="#">Physiological effects</a>
                    <ul class="submenu">
                        <li><a href="graphysio.php">Visualization</a></li>
                        <!-- <li><a href="clean.php">Clean</a></li>
                        <li><a href="clean_default.php">Clean default</a></li> -->
                    </ul>
                </li>
                <li class="menu-1"><a href="#">Settings</a>
                    <ul class="submenu">
                        <li><a href="force_update.php">Update</a></li>
                        <li><a href="backupversion.php">Return to previous version</a></li>
                        <li><a href="updatelog.php">Update Log</a></li>
                        <li><a href="showconfig.php">Show current configuration</a></li>
                        <li><a href="network.php">Network Configuration</a></li>
                    </ul>
                </li>
                <li class="menu-1"><a href="#" id="dump_btn" onclick="displayFilename()">Dump all tables</a></li>
                <li class="menu-1"><a href="treeviewobjects.php" id="tree_btn">Show tree view objects</a></li>
                <li class="menu-1"><a href="../index.php?v=d&p=dashboard">Back to Jeedom</a>
                </li>
            </ul>
        </nav>
        <div id="table_div"></div>
        <div id="filename_modal" class="modal">
            <div class="modal-content">
                <span class="close" id="close_filename" onclick="closeFilename()">&times;</span>
                <p>Please enter the filename where you want the tables to be dumped in (located in /home/pi): </p>
                <form action="dumptables.php" method="post">
                    <input type="text" placeholder="Enter filename" name="filename" id="filename" required>
                    <button type="submit" name="submit_filename">Save file</button>
                </form>
            </div>
        </div>
        <div id="confirm_modal" class="modal">
            <div class="modal-content">
                <span class="close" id="close_confirm" onclick="closeConfirm()">&times;</span>
                <p>Are you sure you want to remove this entry ?</p>
                <form action="delete.php" method="post">
                    <input type="hidden" name="db_id2" id="db_id2" value="">
                    <button type="submit" name="yes_btn" class="yes_btn">Yes</button>
                    <button type="button" name="no_btn" class="no_btn" onclick="closeConfirm()">No</button>
                </form>
            </div>
        </div>
    </body>
</html>

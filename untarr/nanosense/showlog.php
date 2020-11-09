<!DOCTYPE html>
<html>
    <head>
        <title> Last Post </title>
        <link rel="stylesheet" type="text/css" href="main.css">
        <meta charset="utf-8">
    </head>

    <body>

    <a href="main.php">
        <img src="logo-nano.png">
    </a>

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


        <br> <strong> Last Post : </strong> <br>

        <?php 
            $current_time = array();
            $ret_exec = 0;
            exec("date", $current_time, $ret_exec);
            $timee = $current_time[0];
            echo "<i> Current time for the gateway is : <u> $timee </u> <br> format : <br> <u> date alias-pollutant<br> address where posted </u> </i> <br><br>";
            
            $output_exec = array();
            $ret_exec = 0;
            exec("sudo tail -n 40 /var/log/postdata.log", $output_exec, $ret_exec);

            $nb_lines = count($output_exec);
            for ($i = 0 ; $i < $nb_lines ; $i++) {
                echo $output_exec[$i] . "<br>";
            }
        ?>


        <br> <strong> Last Error : </strong> <br>

        <?php 
            echo "<i> Current time for the gateway is : <u> $timee </u> <br> format : <u> date alias pollutant address HTTP-code </u> </i> <br><br>";

            $output_exec = array();
            $ret_exec = 0;
            exec("sudo tail -n 20 /var/log/postdata_error.log", $output_exec, $ret_exec);

            $nb_lines = count($output_exec);
            for ($i = 0 ; $i < $nb_lines ; $i++) {
                echo $output_exec[$i] . "<br>";
            }
        ?>


    </body>
</html>





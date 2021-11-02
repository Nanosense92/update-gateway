<!DOCTYPE html>
<html>
    <head>
        <title> Update </title>
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



<br> Clic on the button below to Update your gateway <br>
This operation will take 2 to 15 minutes <br> <br>

<form action="update.php" method="post">
    <input type="hidden" name="purge" value="run">
    <input type="submit" value="Update now">
</form>



</body>
</html>


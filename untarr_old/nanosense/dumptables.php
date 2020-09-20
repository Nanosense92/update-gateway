<?php
/*
 * script that dump all jeedom db tables in a file to backup the db
 */
if (isset($_POST['filename'])){
    $filename = $_POST['filename'];
    $output = array();
    $ret = 0;
    exec("sudo mysqldump jeedom > $filename.sql", $output, $ret);
    exec("chown pi:pi $filename.sql");
    exec("chmod 775 $filename.sql");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title> Download dumped database </title>
        <link rel="stylesheet" type="text/css" href="graphs.css">
        <meta charset="utf-8">
        <div>
            <div class="topleft">
                <a href="main.php">
                    <img src="logo-nano.png">
                </a>
            </div>
            <center>
                <a href="dumptables.php">
                    <img src="nano-header.png" width="322" height="63">
                </a>
            </center>
        </div>
    </head>
    <body>
        <div id="download_div" class="padding">
            <center>
                <p>Download the .sql file containing the dumped database by clicking on this button:</p>
                <button class=button" type="button" name="DLButton" id="DLButton">
                    <a id="DLElem">Download</a>
                </button>
            </center>
        </div>
    </body>
    <script type="text/javascript">
        var dlbutton = document.getElementById('DLElem');
        dlbutton.setAttribute("href", "<?php echo $filename?>.sql");
        dlbutton.setAttribute("download", "<?php echo $filename?>.sql");
    </script>
</html>

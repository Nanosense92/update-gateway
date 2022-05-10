<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Import-Export EnOcean</title>
    
    </head>
    
    <body  style="background-color:#C8C8C8;" >
    <p> IMPORT/EXPORT ENOCEAN <br> <br> </p>

    <p> 1) Upload a configuration file <br> </p>
    <p> 2) Click on "Import Configuration" <br> </p>
    <p> 3) The configuration of your domotic gateway should take about 5 minutes, please DO NOT use any page/fonctionnality until the configuration is done <br> </p>
    <br>

    <form action="import_export.php" method="post" enctype="multipart/form-data" style="margin-top: 12px;">
        Select a file to upload:
        <input type="file" name="file_name" id="id_file">
        <input type="submit" name="submit"  value="Upload file">
    </form>


    

    <br> <br>

    <!-- <button name="Name of the button" string="Showable label" type="object" confirm="Are you sure you want to do this?"/> -->


    </body>
</html>



<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if ( isset($_POST["submit"]) ) {
 
    $tmp_name = $_FILES["file_name"]["tmp_name"];

    if ( move_uploaded_file($tmp_name, "/var/www/html/nanosense/uploads/import_jeedom_config.json") !== true ) {
        echo "FATAL ERROR MOVE_UPLOADED_FILE() FAILED\n<br>";
        exit ;
    }

    echo '<form action="import_export/import.php" method="post">
    <input type="submit" name="foo" value="Import Configuration" onclick="return confirm(\'Click OK to start (this operation will take ~5 minutes)\')" />
    </form>';

}

?>









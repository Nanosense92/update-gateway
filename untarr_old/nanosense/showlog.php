<?php 
echo nl2br("Log raw data:\n");
echo nl2br(file_get_contents('/home/pi/getdata.log'));
echo nl2br("\n");
echo nl2br("Log physio data:\n");
echo nl2br(file_get_contents('/home/pi/postphysio.log'));
?>
<!DOCTYPE html>
<html>
    <head>
        <title> Last log </title>
    </head>
</html>

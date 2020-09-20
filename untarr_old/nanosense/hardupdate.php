<?php
$strJsonFileContents = file_get_contents('/home/pi/Nano-Setting.json');
$array = json_decode($strJsonFileContents, true);
foreach($array AS $key => $value)
{
    if($key == 'version'){
        $array[$key][0] = 0;}
}
$newjson = json_encode($array,JSON_PRETTY_PRINT);
file_put_contents('/home/pi/Nano-Setting.json', $newjson);

include '/var/www/html/nanosense/update.php';

header('Location:main.php');
?>

<?php
if (isset($_POST['save_config'])){
    // get the parameters of the firmware configuration
    $auto_update = $_POST['auto-update'];
    $timezone_offset = $_POST['timezone-offset'];
    $send_data_interval = $_POST['send-data-interval'];
    $average_mode = $_POST['average-mode'];

    // DEBUG
    /*
    var_dump($auto_update);
    var_dump($timezone_offset);
    var_dump($send_data_interval);
    var_dump($average_mode);
    */
    // !DEBUG

    // get json file contents in a json object
    $jsoncontents = file_get_contents('/home/pi/Nano-Setting.json');
    $json = json_decode($jsoncontents, true);
    
    // change the parameters in the json object
    foreach($json AS $key => $value){
        if ($key == 'timezone'){
            $json[$key] = $timezone_offset;
        }
        else if ($key == 'send-data-interval'){
            $json[$key] = $send_data_interval;
        }
        else if ($key == 'auto-update'){
            if ($auto_update == 'true'){
                $json[$key][0] = 1;
            }
            else{
                $json[$key][0] = 0;
            }
        }
        else if ($key == 'average-mode'){
            if ($average_mode == 'true'){
                $json[$key][0] = 1;
            }
            else{
                $json[$key][0] = 0;
            }
        }
    }
    
    // rewrite the json object in the json file
    $newjsoncontents = json_encode($json, JSON_PRETTY_PRINT);
    file_put_contents('/home/pi/Nano-Setting.json', $newjsoncontents . PHP_EOL);
    
    // modify crontab file
    $regex_number = '([1-5]{1}[0-9]{1}|60|[1-9])'; // match a number between 1 and 60 included
    $regex_crontab = '^(\*\/)' . $regex_number;
    $sed_command = 'sudo sed -Ei "s/' . $regex_crontab . '/'
        . '\1' . $send_data_interval . '/" /var/spool/cron/crontabs/pi';
    //var_dump($sed_command);
    exec($sed_command);

    // reload crontab file
    exec('sudo crontab -u pi -l | sudo crontab -u pi -');

    header('Location:main.php');
}
?>

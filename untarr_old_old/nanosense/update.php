<?php
$git = array();
$script = array();
$ret = 0;

$log_dir = '/var/log/';
$log_file = $log_dir . 'update.log';

$json = file_get_contents('/home/pi/Nano-Setting.json');
$jsondecode = json_decode($json, true);
$auto_update = NULL;
/*
foreach ($jsondecode AS $key => $value){
    if ($key == 'auto-update'){
        $auto_update = (int)$value;
    }
}
*/

$auto_update = (int)$jsondecode['auto-update'];


chdir('/home/pi/');
exec('rm -rf ./update-gateway ./email');
if ($auto_update == 1){
    exec('echo "$(date): starting update" >> ' . $log_file);
    exec('git clone https://github.com/Nanosense92/update-gateway.git update-gateway/', $git, $ret);
    if ($ret != 0){
        exec('echo "$(date): git clone error" >> ' . $log_file);
        echo exec('rm -rf update-gateway/');
        exit;
    }
    else{
        exec('echo "$(date): repository cloned" >> ' . $log_file);
    }
    exec("dos2unix update-gateway/shasum.txt");
    exec("bash -c 'diff <(shasum update-gateway/update.tar) update-gateway/shasum.txt'", $script, $ret);
    if ($ret != 0){
        exec('echo "$(date): corrupted data in update" >> ' . $log_file);
        echo exec('rm -rf update-gateway/');
        header('Location:main.php');
        exit;
    }
    else{
        exec('echo "$(date): data validated" >> ' . $log_file);
    }

    exec('tar -xzvf update-gateway/update.tar -C update-gateway/', $script, $ret);
    if ($ret != 0){
        exec('echo "$(date): decompression failed" >> ' . $log_file);
        echo exec('rm -rf update-gateway/');
        header('Location:main.php');
        exit;
    }
    else{
        exec('echo "$(date): decompressing update archive" >> ' . $log_file);
    }

    exec('sh update-gateway/update-script.sh', $script, $ret);
    if ($ret != 0){
        exec('echo "$(date): update script failed" >> ' . $log_file);
        echo exec ('rm -rf update-gateway/  email');
        header('Location:main.php');
        exit;
    }
    else{
        exec('echo "$(date): update successfully installed" >> ' . $log_file);
    }

    echo exec('rm -rf update-gateway/  email');
}
else{
    exec('echo "$(date): auto-update is disabled" >> ' . $log_file);
}

header('Location:main.php');
?>

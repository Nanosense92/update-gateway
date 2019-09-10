<?php
$git = array();
$script = array();
$ret = 0;

exec ('sudo git clone https://github.com/Nanosense92/update-gateway.git', $git, $ret);
if ($ret != 0) {
	exec('echo "git clone error" > update_log.log');
}
else {
	exec('echo "repository cloned" > update_log.log');
}


exec ('sudo sh update-gateway/update-script.sh', $script);
if ($ret != 0) {
	exec('echo "update failed" > update_log.log');
}
exec ("sudo rm -rf update-gateway");

header("Location:main.php");

exit;

?>

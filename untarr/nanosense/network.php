<!DOCTYPE html>
<html>
    <head>
        <title> Network Configuration </title>
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
                    <li><a href="clean.php">Clean</a></li>
                    <li><a href="clean_default.php">Clean default</a></li>
                </ul>
            </li>
            <li class="menu-1"><a href="#">Settings</a>
                <ul class="submenu">
                    <li><a href="update.php">Update</a></li>
                    <li><a href="changelog.php">Changelog</a></li>
                    <li><a href="hardupdate.php">Hard Update</a></li>
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
















<?php
    error_reporting(E_WARNING);
    $network_was_added = "";


    function add_new_network_in_wpa_config_file($ssid, $psk, &$network_was_added)
    {
        $new_network_str = "\nnetwork={\n\tssid=\\\"" . $ssid . "\\\"" . "\n\tpsk=\\\"" . $psk . "\\\"\n}\n";

        $exec_output = array();
        $exec_ret_val = 0;
        exec("sudo echo \"" . $new_network_str . "\" | sudo tee --append /etc/wpa_supplicant/wpa_supplicant.conf", $exec_output, $exec_ret_val);
        if ($exec_ret_val !== 0) {
            echo "FATAL ERROR OCCURED : write to conf file failed";
            exit ;
        }

        $network_was_added = "The new network was successfully added - The gateway will try to connect to it after a reboot";

    }

?>



<?php
    $user_submitted = false;
    $wifi_is_up = false;
    $button_value = "";

    $exec_output = array();
    $exec_ret_val = 0;
    exec("sudo /usr/sbin/rfkill unblock 0", $exec_output, $exec_ret_val);
    if ($exec_ret_val !== 0) {
        echo "RFKILL UNBLOCK DID NOT WORK";
        exit ;
    }

    exec("ip address show | grep \"wlan\" | grep --only-matching \"UP\"", $exec_output, $exec_ret_val);
    if ($exec_ret_val === 0) {
        $button_value = "Deactivate WiFi";
        $wifi_is_up = true;
    }
    else {
        $button_value = "Activate WiFi";
        $wifi_is_up = false;
    }

    if ( ! empty($_GET['act']) ) 
    {
        $up_or_down = ($wifi_is_up === true) ? "down" : "up";

        exec("sudo ip link set wlan0 " . $up_or_down);
        sleep(5);
        $_GET['act'] = "";
        unset( $_GET['act'] );
        header("Location: ./network.php");
        //echo "Hello world!";
    }

    $_GET['add_ssid'] = trim($_GET['add_ssid']);
    $_GET['add_psk'] = trim($_GET['add_psk']);
    if ( ! empty($_GET['add_ssid']) && ! empty($_GET['add_psk']) )
    {
        add_new_network_in_wpa_config_file($_GET['add_ssid'], $_GET['add_psk'], $network_was_added);
    } 
    else
    {
       // at least one of the two fields was empty
      // $network_was_added = "To add a new network you have to fill the two fields (name and password)";
    }

    if ( ! empty($_GET['reboot_now']) ) 
    {
        header("Location: ./reboot_msg.php");
        exec("sudo reboot now");   
    }


    if ( ! empty($_GET['purge']) ) {
        $number_of_lines_to_keep = 0;

        $exec_output = array();
        $exec_ret_val = 0;
        exec("sudo grep -n -m 1 'network' /etc/wpa_supplicant/wpa_supplicant.conf | cut -d ':' -f 1", $exec_output, $exec_ret_val);
        $number_of_lines_to_keep = $exec_output[0];
        $number_of_lines_to_keep = $number_of_lines_to_keep - 1;

        exec(
        "sudo head -n $number_of_lines_to_keep /etc/wpa_supplicant/wpa_supplicant.conf > ./.tmp_lines_to_keep "
        . "&& cat ./.tmp_lines_to_keep | sudo tee /etc/wpa_supplicant/wpa_supplicant.conf"
        , $exec_output, $exec_ret_val);
        

        // extraire les lignes qui sont avant la premiere occurrence de "network" et les mettre dans un fichier tmp
        // delete le fichier principal
        // renommer le fichier tmp avec le nom du fichier principal
    }
  
?>

 

    
    <h3> Network Configuration </h3>

    <!-- (de)activate WiFi -->
    <form action="network.php" method="get">
        <input type="hidden" name="act" value="run">
        <input type="submit" value="<?php echo $button_value ?>">
    </form>

    <br>
    <hr>

    <h4> Add new WiFi network </h4>
    <form action="network.php" method="get">
        Enter new network's name (SSID)
        <input type="input" name="add_ssid" value="">
        <br>
        Enter new network's password
        <input type="input" name="add_psk" value="">
        <input type="submit" value="Add new network">
    </form>

    <p style="color:<?php echo ($network_was_added[1] === 'o') ? "red" : "green"?>;"> <strong>
<?php
    echo $network_was_added;
?>
   </strong> </p>


<?php
    if ( true /* strlen($network_was_added) > 0 */ ) {
        echo
        "<form action=\"network.php\" method=\"get\">"
        . "<input type=\"hidden\" name=\"reboot_now\" value=\"runn\">"
        . "<input type=\"submit\" value=\"Reboot now\">"
        . "</form>"
        ;
    }
?>

<br>
<hr> 

<h4> Choose a Static IP address </h4>

<hr> 

<h4> Stop using a Static IP address and use DHCP instead </h4>

<hr>

<h4> Already configured networks : </h4>
<?php
    $exec_output = array();
    exec("cat /etc/wpa_supplicant/wpa_supplicant.conf | grep ssid | cut -d '\"' -f 2", $exec_output);
    $nb_nw = count($exec_output);
    echo "<p><i>";
    for ($i = 0 ; $i < $nb_nw ; $i++) {
        echo $exec_output[$i];
        echo "<br>";
    }
    echo "</i></p>";
?>

    <form action="network.php" method="get">
        <input type="hidden" name="purge" value="run">
        <input type="submit" value="Delete all configured networks">
    </form>
     
    </body>
</html>




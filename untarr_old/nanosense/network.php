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


<div class="la_marge">





<?php
    error_reporting(E_WARNING);

    function add_static_ip_address($ip_addr, $interface)
    {
        // si y'a deja une IP fixe de configuree alors je mets une erreur et je fais rien de plus
        $exec_out = array();
        $exec_ret_val = 0;
        exec("sudo grep  'nanosense' /etc/dhcpcd.conf | grep '$interface'", $exec_out, $exec_ret_val);
        if ($exec_ret_val === 0) {
            return FALSE;
        }

        // trouver le mask 
        // trouver ip routeur

        $mask = "";
        $router_ip = "";

        /* FIND MASK */
        $exec_output = array();
        $exec_ret_val = 0;
        exec("sudo ip address | grep --after-context=3 eth0 | grep -m 1 inet | cut -d '/' -f 2 | cut -d ' ' -f 1", $exec_output, $exec_ret_val);
        if ($exec_ret_val !== 0) {
            echo "FATAL ERROR OCCURED : Failed to find eth0 IP mask";
            exit ;
        }

        //echo "count = " . count($exec_output);
        if ( count($exec_output) === 0 ) {  // if no mask was found, probably because interface is down
            exec("sudo ip address | grep --after-context=3 wlan0 | grep -m 1 inet | cut -d '/' -f 2 | cut -d ' ' -f 1", $exec_output, $exec_ret_val);
            if ($exec_ret_val !== 0) {
                echo "FATAL ERROR OCCURED : Failed to find wlan0 IP mask";
                exit ;
            }

            if ( count($exec_output) === 0 ) {
                echo "FATAL ERROR OCCURED : Failed to find wlan0 or eth0 IP mask";
                exit ; 
            }

        } // end if ( count() )

        $mask = $exec_output[0];
//echo "MASK = $mask <br>";

        /* FIND ROUTER IP */
        $exec_output2 = array();
        exec("sudo ip route | grep -m 1 default | cut -d ' ' -f 3", $exec_output2, $exec_ret_val);
        if ( $exec_ret_val !== 0 || count($exec_output2) === 0 || ip2long($exec_output2[0]) === FALSE ) {
            echo "FATAL ERROR OCCURED : Failed to find router's IP ; (found '$exec_output2[0]')";
            exit ;
        }

        $router_ip = $exec_output2[0];
//echo "ROUTER IP = $router_ip <br>";


        $string_to_add_to_conf_file = 
        "\n#nanosense " . $interface . "\n"
        . "interface ". $interface . "\n"
        . "static ip_address=" . $ip_addr . "/" . $mask . "\n"
        . "static routers=" . $router_ip . "\n"
        . "static domain_name_servers=" . $router_ip . " 8.8.8.8\n"
        ;

//echo "<br> <pre> STR = |$string_to_add_to_conf_file|  </pre> <br>";

        unset($exec_output);
        $exec_output = array();
        $exec_ret_val = 0;
        exec("sudo echo '$string_to_add_to_conf_file' | sudo tee --append /etc/dhcpcd.conf", $exec_output, $exec_ret_val);
        if ($exec_ret_val !== 0) {
            echo "FATAL ERROR OCCURED : write to /etc/dhcpcd.conf file failed";
            exit ;
        }

/* 
        #nanosense
        interface eth0
        static ip_address=192.168.0.44/24
        static routers=192.168.0.254
        static domain_name_servers=192.168.0.254 8.8.8.8

        interface wlan0
        static ip_address=192.168.0.22/24
        static routers=192.168.0.254
        static domain_name_servers=192.168.0.254 8.8.8.8
*/

        return TRUE;
    } // end func

?>



<?php
    $network_was_added = "";
    $static_ip_eth0_already_configured = FALSE;
    $static_ip_wlan0_already_configured = FALSE;
    $wifi_ip_address_is_not_well_formatted = FALSE;
    $eth_ip_address_is_not_well_formatted = FALSE;
    $static_ip_eth0_is_ready = FALSE;
    $static_ip_wlan0_is_ready = FALSE;
    $dhcp_mode_is_now_active = FALSE;


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

    if ( ! empty($_POST['act']) ) 
    {
        $up_or_down = ($wifi_is_up === true) ? "down" : "up";

        exec("sudo ip link set wlan0 " . $up_or_down);
        sleep(5);
        $_POST['act'] = "";
        unset( $_POST['act'] );
        header("Location: ./network.php");
        //echo "Hello world!";
    }

    $_POST['add_ssid'] = trim($_POST['add_ssid']);
    $_POST['add_psk'] = trim($_POST['add_psk']);
    if ( ! empty($_POST['add_ssid']) && ! empty($_POST['add_psk']) )
    {
        add_new_network_in_wpa_config_file($_POST['add_ssid'], $_POST['add_psk'], $network_was_added);
    } 
    else
    {
       // at least one of the two fields was empty
      // $network_was_added = "To add a new network you have to fill the two fields (name and password)";
    }

    if ( ! empty($_POST['reboot_now']) ) 
    {
        header("Location: ./reboot_msg.php");
        sleep(5);
        exec("sudo reboot now");   
    }


    if ( ! empty($_POST['purge']) ) {
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
    }

    if ( ! empty($_POST['add_static_wifi']) ) {
        $ip_addr = $_POST['add_static_wifi'];

        if ( ip2long($ip_addr) === FALSE ) {
            $wifi_ip_address_is_not_well_formatted = TRUE;
        }

        else {
            $ret = add_static_ip_address($ip_addr, "wlan0");
            if ($ret === FALSE) {
                $static_ip_wlan0_already_configured = TRUE;
            }
            else if ($ret === TRUE) {
                $static_ip_wlan0_is_ready = TRUE;
            }
        }

    }
  
    if ( ! empty($_POST['add_static_ethernet']) ) {
        $ip_addr = $_POST['add_static_ethernet'];

        if ( ip2long($ip_addr) === FALSE ) {
            $eth_ip_address_is_not_well_formatted = TRUE;
        }

        else {
            $ret = add_static_ip_address($ip_addr, "eth0");
            if ($ret === FALSE) {
                $static_ip_eth0_already_configured = TRUE;
            }
            else if ($ret === TRUE) {
                $static_ip_eth0_is_ready = TRUE;
            }
        }
    }


    if ( ! empty($_POST['go_dhcp']) ) {
        $exec_ret_val = 0;
        unset($exec_output);
        $exec_output = array();
        exec("sudo sed --in-place='.sed_backup'  '/nanosense/,\$d'  /etc/dhcpcd.conf", $exec_output, $exec_ret_val); 
        if ($exec_ret_val !== 0) {
            exec("sudo cp dhcpcd.conf.sed_backup  /etc/dhcpcd.conf");
            echo "FATAL ERROR OCCURED : Failed to edit /etc/dhcpcd.conf with sed";
            exit ;
        }
        $dhcp_mode_is_now_active = TRUE;
    }

?>

 

    
    <h3> Network Configuration </h3>

    <!-- (de)activate WiFi -->
    <form action="network.php" method="post">
        <input type="hidden" name="act" value="run">
        <input type="submit" value="<?php echo $button_value ?>">
    </form>

    <br>
    <hr>

    <h4> Add new WiFi network </h4>
    <form action="network.php" method="post">
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
        "<form action=\"network.php\" method=\"post\">"
        . "<input type=\"hidden\" name=\"reboot_now\" value=\"runn\">"
        . "<input type=\"submit\" value=\"Reboot now\">"
        . "</form>"
        ;
    }
?>

<br>
<hr> 

<h4> Choose a Static IP address </h4>

    <i> If the WiFi is configured with a Static IP address and Ethernet is not, then Ethernet may not work properly (and vice versa). 
    <br> If both network interfaces are configured with a Static IP address, both WiFi and Ethernet will work fine. </i>
    <br> <br>

<?php
    unset($exec_out);
    $exec_out = array();
    $exec_ret_val = 0;
    exec("sudo grep  'nanosense' /etc/dhcpcd.conf | grep 'wlan0'", $exec_out, $exec_ret_val);
    if ($exec_ret_val !== 0) {
        echo "<p style=\"font-style:italic;\">"
        . "Static IP address for WiFi (wireless) is currently <span style=\"color:red; font-style:bold\">INACTIVE</span>" 
        . "</p>";
    }
    else {
        unset($exec_out);
        $exec_out = array();
        $exec_ret_val = 0;
        exec("sudo grep --after-context=2  'nanosense wlan0'  /etc/dhcpcd.conf | sudo grep 'ip_address' | cut -d '=' -f 2", $exec_out, $exec_ret_val);
        echo "<p style=\"font-style:italic;\">"
        . "Static IP address for WiFi (wireless) is currently <span style=\"color:green; font-style:bold\">ACTIVE</span>" 
        . " (" . $exec_out[0] . ")" . "</p>";    
    }

    unset($exec_out);
    $exec_out = array();
    $exec_ret_val = 0;
    exec("sudo grep  'nanosense' /etc/dhcpcd.conf | grep 'eth0'", $exec_out, $exec_ret_val);
    if ($exec_ret_val !== 0) {
        echo "<p style=\"font-style:italic;\">"
        . "Static IP address for Ethernet (wired) is currently <span style=\"color:red; font-style:bold\">INACTIVE</span>" 
        . "</p>";    }
    else {
        unset($exec_out);
        $exec_out = array();
        $exec_ret_val = 0;
        exec("sudo grep --after-context=2  'nanosense eth0'  /etc/dhcpcd.conf | sudo grep 'ip_address' | cut -d '=' -f 2", $exec_out, $exec_ret_val);
        echo "<p style=\"font-style:italic;\">"
        . "Static IP address for Ethernet (wired) is currently <span style=\"color:green; font-style:bold\">ACTIVE</span>"     
        . " (" . $exec_out[0] . ")" . "</p>"; 
    }
?>

    <br> <br>

    <form action="network.php" method="post">
        Enter a Static IP address to use for WiFi (X.X.X.X)
        <input type="input" name="add_static_wifi" value="">
        <input type="submit" value="Use Static IP address for WiFi (wireless)">
    </form>

<?php
    if ($wifi_ip_address_is_not_well_formatted === TRUE) {
        echo "<p style=\"color:red;\"> <strong> The IP address you entered is not well formatted (example: 192.168.0.42) </strong> </p> <br>";
    }
    if ($static_ip_wlan0_already_configured === TRUE) {
        echo "<p style=\"color:red;\"> <strong> A static IP address for WiFi is already configured ; You can remove it with the appropriate button then add a new one </strong> </p> <br>";
    }
    if ($static_ip_wlan0_is_ready === TRUE) {
        echo "<p style=\"color:green;\"> <strong> A static IP address for WiFi has been added ; It will be effective after a reboot </strong> </p> <br>";
    }
?>
    <br>

    <form action="network.php" method="post">
        Enter a Static IP address to use for Ethernet (X.X.X.X)
        <input type="input" name="add_static_ethernet" value="">
        <input type="submit" value="Use Static IP address for Ethernet (wired)">
    </form>

<?php
    if ($eth_ip_address_is_not_well_formatted === TRUE) {
        echo "<p style=\"color:red;\"> <strong> The IP address you entered is not well formatted (example: 192.168.0.42) </strong> </p> <br>";
    }
    if ($static_ip_eth0_already_configured === TRUE) {
        echo "<p style=\"color:red;\"> <strong> A static IP address for Ethernet is already configured ; You can remove it with the appropriate button then add a new one </strong> </p> <br>";
    }
    if ($static_ip_eth0_is_ready === TRUE) {
        echo "<p style=\"color:green;\"> <strong> A static IP address for Ethernet has been added ; It will be effective after a reboot </strong> </p> <br>";
    }
?>

<?php
    if ( true ) {
        echo
        "<br>"
        . "<form action=\"network.php\" method=\"post\">"
        . "<input type=\"hidden\" name=\"reboot_now\" value=\"runn\">"
        . "<input type=\"submit\" value=\"Reboot now\">"
        . "</form>"
        ;
    }

    if ( true ) {
        echo
        "<br> <br>"
        . "<form action=\"network.php\" method=\"post\">"
        . "<input type=\"hidden\" name=\"go_dhcp\" value=\"runnn\">"
        . "<input type=\"submit\" value=\"Deactivate Static IP and use DHCP mode instead\">"
        . "</form>"
        ;
    }

    if ($dhcp_mode_is_now_active === TRUE) {
        echo "<p style=\"color:green;\"> <strong> The Static IP addresses for WiFi and Ethernet are disabled. The router will choose IP addresses (DHCP) ; It will be effective after a reboot </strong> </p> <br>";
    }
?>


<hr>

<h4> Already configured networks : </h4>
<?php
    $exec_output = array();
    exec("sudo cat /etc/wpa_supplicant/wpa_supplicant.conf | grep ssid | cut -d '\"' -f 2", $exec_output);
    $nb_nw = count($exec_output);
    echo "<p><i>";
    for ($i = 0 ; $i < $nb_nw ; $i++) {
        echo $exec_output[$i];
        echo "<br>";
    }
    echo "</i></p>";
?>

    <form action="network.php" method="post">
        <input type="hidden" name="purge" value="run">
        <input type="submit" value="Delete all configured networks">
    </form>
     

<!-- la_marge -->
</div> 

    </body>
</html>




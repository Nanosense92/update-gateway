
<?php

//require('general_functions.php');
//FUNCTIONS
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    function print_web($obj) 
    {
        echo "<br> <span style=color:blue;> ";
        echo "<pre>";
        echo "val display -> | $obj | type " . gettype($obj);
        if (gettype($obj) == "array") {
            echo "<br>";
            var_dump($obj);
            echo "<br>";
        }
        echo "</pre>";
        echo "<br>  </span>";
    }

    function display_python_output($output) 
    {
        foreach ($output as $o) {
            //echo "<br>" . $o . "<br>";
            echo $o . "<br>";
        }
    }

    
    function load_db()
    {
        $dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
        if ($dbconnect->connect_errno) {
            printf("Connection to 'jeedom' database failed");
            exit;
        }
        return $dbconnect;
    }

    function close_db($dbconnect)
    {
        mysqli_close($dbconnect);  	
    }
    
    function check_idslave_db($slave_id) 
    {
        $dbconnect = load_db();
        $ret = "notfound";

        $idq = $dbconnect->query("SELECT logicalId FROM eqLogic WHERE logicalId=$slave_id");
        $id = $idq->fetch_array(MYSQLI_BOTH);
    
        if (empty($id['logicalId']) === false) {
            $ret = "found";
        } 
        
        close_db($dbconnect);
        return $ret;
    }

    function add_device_to_eqLogic($dev)
    {
        $dbconnect = load_db();
        
        $sl = $dev['slave_id'];
        $reg = $dev['registers'];
        $ty = $dev['type'];
        $na = $dev['name'];

     //   print_web($reg);
        $res = $dbconnect->query("INSERT INTO eqLogic (logicalId,`status`, eqType_name,`name`, generic_type, isVisible, isEnable) VALUES ('$sl',\"$reg\",'modbus','$na','$ty',1,1)");
      //  echo "this->" . $dbconnect->error;
    
        close_db($dbconnect);
    }

    function delete_device_from_eqLogic($slave_id)
    {
        $dbconnect = load_db();
        $dbconnect->query("DELETE FROM eqLogic WHERE logicalId=$slave_id");	
        echo $dbconnect->error;
        close_db($dbconnect);
    }

    

   
    function update_device_in_eqLogic($alias, $slave_id) {
        $dbconnect = load_db();

        /*$slave_id = $dev['slave_id'];
        $reg = $dev['registers'];
        $ty = $dev['type'];
        $na = $dev['name'];*/

        $dbconnect->query("UPDATE eqLogic SET name='$alias'         WHERE logicalId=$slave_id");	
        //$dbconnect->query("UPDATE eqLogic SET isEnable=1         WHERE logicalId=$slave_id");	
        //$dbconnect->query("UPDATE eqLogic SET isVisible=1        WHERE logicalId=$slave_id");	
        //$dbconnect->query("UPDATE eqLogic SET generic_type='$ty' WHERE logicalId=$slave_id");	
        //$dbconnect->query("UPDATE eqLogic SET status='$reg'      WHERE logicalId=$slave_id");	

        echo "this->" . $dbconnect->error;

        close_db($dbconnect);
    }
/*
    function add_device_to_cmd($dev) 
    {
        $dbconnect = load_db();

        $name = $dev['name'];
        $res_query = $dbconnect->query("SELECT id FROM eqLogic WHERE (`name`='$name')");
        $res = $res_query->fetch_array(MYSQLI_BOTH);
        
        $eqLogic_unik_id = $res['id']; 
        $tmp = array();
        if ($dev['type'] == 'e4000') {

            array_push($tmp, 'Temperature');//name
            array_push($tmp, $name);//eqType
            array_push($tmp, "TMP::value");//logicalId
            array_push($tmp, $eqLogic_unik_id);//eqLogic_id

            array_push($tmp, 'Humidity');//name
            array_push($tmp, $name);//eqType
            array_push($tmp, "HUM::value");//logicalId
            array_push($tmp, $eqLogic_unik_id);//eqLogic_id

            array_push($tmp, 'CO2');//name
            array_push($tmp, $name);//eqType
            array_push($tmp, "CONC::value");//logicalId
            array_push($tmp, $eqLogic_unik_id);//eqLogic_id

            array_push($tmp, 'Total');//name
            array_push($tmp, $name);//eqType
            array_push($tmp, "total");//logicalId
            array_push($tmp, $eqLogic_unik_id);//eqLogic_id

        } else {

            array_push($tmp, 'PM10');//name
            array_push($tmp, $name);//eqType
            array_push($tmp, "PM10::value");//logicalId
            array_push($tmp, $eqLogic_unik_id);//eqLogic_id

            array_push($tmp, 'PM2.5');//name
            array_push($tmp, $name);//eqType
            array_push($tmp, "PM2.5::value");//logicalId
            array_push($tmp, $eqLogic_unik_id);//eqLogic_id

            array_push($tmp, 'PM1');//name
            array_push($tmp, $name);//eqType
            array_push($tmp, "PM1::value");//logicalId
            array_push($tmp, $eqLogic_unik_id);//eqLogic_id
        }

        $i = 0;
        while ($i < count($tmp)) {
            $a = $tmp[$i];
            $b = $tmp[$i + 1];
            $c = $tmp[$i + 2];
            $d = $tmp[$i + 3];
            $dbconnect->query("INSERT INTO cmd (`name`,eqType,logicalId,eqLogic_id) VALUES ('$a', '$b', '$c', $d)");	
            $i += 4;
        }
    
        close_db($dbconnect);
    } 
    */

    function launch_scan_python_script($scan_nb) 
    {
        //detecte sondes et mets leurs valeurs dans data.ini
        exec("sudo /usr/bin/python3.5 modbus_py/main.py $scan_nb 2>&1", $output, $return_value);
       // display_python_output($output);
       // echo $output;
       // $xfile = "modbus_py/modbus__cache/cache_modbus.ini"; 
       // $modbus_cache = parse_ini_file($xfile, true);
       // print_web($modbus_cache);

       // return $modbus_cache;
    }

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

        
        $scan_nb = $_GET['nb_scans'];
        launch_scan_python_script($scan_nb);

        /*OBSOLETE
        foreach ($modbus_cache as $key => $value)  { //24,7    X 4
           // print_web($key);
            //print_web($value);


            //vide msg erreur
            
            $slave_id = $value['slave_id'];
            
            if (check_idslave_db($slave_id) === "found") {
               // echo "xUPDATE";
               //update_device_in_eqLogic($alias);
               // delete_device_from_eqLogic($slave_id);
            } 
            else {
            //add_device_to_eqLogic($value);
            //add_device_to_cmd($value);
            }
        }*/


?>

<script>
    window.location.replace("main.php");
</script>





 
<!DOCTYPE HTML>  
<html>
<head>
    <script src="jkwery.js"></script>
        

</head>
<body style="background-color:#b3edff;">


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     
    <link rel="stylesheet" href="css/pure-min.css">
    <link rel="stylesheet" href="css/grids-responsive-min.css"> 
    <link rel="stylesheet" href="css/display_values_real_time.css">



    


<?php

function en_date_to_fr_date($en_date)
{
    $fr_date = "";
    $en_date_len = strlen($en_date);

    $tmp = "";
    for ($i = 0 ; $en_date[$i] !== ' ' ; $i++) {
        $tmp = $tmp . $en_date[$i];
    }
    $en_date = $tmp;
    //echo $en_date;

    $en_date_len = strlen($en_date);

    for ($i = $en_date_len ; $i > -1 ; $i--) {
        if ($en_date[$i] === '-') {
            for ($j = $i + 1 ; $j < $en_date_len && $en_date[$j] !== '-' ; $j++ ) {
                $fr_date = $fr_date . $en_date[$j];
            }
            $fr_date = $fr_date . '-';
        }
    }
    
    for ($i = 0 ; $en_date[$i] !== '-' ; $i++) {
        $fr_date = $fr_date . $en_date[$i];
    }

    return $fr_date;
}

function get_slave_id($device_raw_name)
{
    $slave_id = (int)0;
    $str_len = strlen($device_raw_name);

    for ($i = 0 ; $i < $str_len ; $i++) {
        if ( is_numeric($device_raw_name[$i]) === false )
            break;
        else
            $slave_id = ($slave_id * 10) + ((int)($device_raw_name[$i]));
    }

    return $slave_id;
}

function get_alias($slave_id)
{
    $alias = "";

    $dbconnect = mysqli_connect("localhost", "jeedom", "85522aa27894d77", "jeedom");
	if ($dbconnect->connect_errno) {
		printf("Connection to 'jeedom' database failed");
		exit(1);
    }
    
    $res_query = $dbconnect->query("SELECT `name` FROM eqLogic WHERE logicalID = $slave_id ORDER BY id DESC LIMIT 1");
	if ($res_query === false) {
		echo "<br>fail to connect to Jeedom database<br>";
		exit(1);
	}
    $alias_array = $res_query->fetch_array(MYSQLI_BOTH);
    $alias = $alias_array['name'];

    mysqli_close($dbconnect);

    return $alias;
}


$ret_parse_ini = parse_ini_file("/home/pi/modbus-gateway/modbus__cache/data.ini", true);
if ($ret_parse_ini === false) {
    echo "parse_ini_file() failed";
    exit(2);
}

// echo "<pre>" ; print_r($ret_parse_ini); echo "</pre>" ;
// echo "<br><br>";

// foreach ($ret_parse_ini as $key => $value) {

//     echo "key = $key<br>";
//     echo "value = <br>";
//     echo "<pre>" ; print_r($value); echo "</pre>" ;
//     echo "<br><br>";

// }

$there_is_something_to_display = false;
foreach ($ret_parse_ini as $key => $value) {
    $there_is_something_to_display = true;

    $slave_id = get_slave_id($key);
    $alias = get_alias($slave_id);

    $date = en_date_to_fr_date($value['date']);
   // echo "date = $date <br>";

    $measured = $value['name'];
    if ($measured === "Total")
        $measured = "Total COV";

    $measured_value = $value['val'];

    $unit = $value['unit'];
    if ($unit === "%%")
        $unit = "%";
    else if ($unit === "C")
        $unit = "°C";
    
    echo "
    <div class=\"box pure-g\">
        <div class=\"pure-u-1 pure-u-md-1-3\">
             <div class=\"box box_background\">   
                <div class=\"box_background_again\">
                    <h2> $date </h2>
                    <span class=\"value_measured\">
                        $measured_value <span> $unit </span>
                       <span> $alias - ($measured) </span>
                       <h1> slave id: $slave_id </h1>
                    </span>
                </div>
            </div>
        </div>
    </div>
    ";

}

if ($there_is_something_to_display === false) {
    echo "Aucune valeur à afficher";
}

?>



<!-- <div class="l-content"> -->
    <!-- <div class="pricing-tables pure-g">
        <div class="pure-u-1 pure-u-md-1-3">
             <div class="pricing-table pricing-table-free">   
                <div class="pricing-table-header">
                    <h2> Alias - Type (pm 2.5)</h2>

                    <span class="pricing-table-price">
                        Value <span>Unit</span>
                    </span>
                </div>
                <ul class="pricing-table-list">
                    <li>Date</li>
                    <li>Slave id</li>
                </ul>
            </div>
        </div>
    </div>  -->
<!-- </div> end l-content -->

</body>
</html>




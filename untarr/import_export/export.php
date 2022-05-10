<?php

require_once "/home/pi/enocean-gateway/get_database_password.php";

/* Connect to jeedom database */
$dbconnect = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');
if ($dbconnect === FALSE || $dbconnect->connect_errno) {
    echo "FATAL ERROR: Connection to 'jeedom' database failed\n";
    exit ;
}

/********  EQUIPMENT ********/

/* SQL Query to get the Equipments */
$ret_sql_query_equipment = $dbconnect->query("SELECT `name`, `logicalId`, `object_id`, `configuration` FROM `eqLogic` WHERE `eqType_name`='openenocean'");
if ($ret_sql_query_equipment === FALSE) {
    echo "FATAL ERROR: Query to 'jeedom' database failed (equipment)\n";
    mysqli_close($dbconnect);
    exit ;
}

$number_of_equipment = 0;
$equipment_array = array();

/* Loop that iterates over the rows resulting from the SQL query  (iterates through every enocean equipment) */
while ( $ret_sql_query_equipment_row = $ret_sql_query_equipment->fetch_array(MYSQLI_BOTH) ) {

    $number_of_equipment += 1;

    /* Decode JSON format from 'configuration' SQL row field (the goal is to find the EEP) */
    $ret_json_decode = json_decode($ret_sql_query_equipment_row['configuration'], $assoc = TRUE);
    if ($ret_json_decode === NULL) {
        echo "FATAL ERROR: json_decode() failed to decode 'configuration' sql field\n";
        mysqli_close($dbconnect);
        exit ;
    }
    $eep = $ret_json_decode['device'];

    /* find the object containing the equipment */
    $ret_sql_query_object = $dbconnect->query("SELECT `name` from `object` where id=" . $ret_sql_query_equipment_row['object_id']);
    // returns FALSE if the equipment has no object ; so I don't know how to properly check the query execution, I did not search yet
    if ($ret_sql_query_object === FALSE) { // no object
        $object = "";
    }
    else {
        $object = $ret_sql_query_object->fetch_array(MYSQLI_BOTH)['name'];
    }

    // echo "alias = <" . $ret_sql_query_equipment_row['name'] . ">\n";
    // echo "id = <" . $ret_sql_query_equipment_row['logicalId'] . ">\n";
    // echo "object = <" . $object . ">\n";
    // echo "eep = <" . $eep . ">\n";

    $equipment_array[] = array(
        'probe_model' => "",
        'alias' => $ret_sql_query_equipment_row['name'],
        'id' => $ret_sql_query_equipment_row['logicalId'],
        'object' => $object,
        'eep' => $eep
     );

    //echo "\n";
} // end while()


//echo "---------------------------\n\n";
/******** REMOTE DATABASES ********/

/* SQL Query to get the Databases */
$sql_query_databases = "SELECT `login`, `password`, `addr`, `port`, `path`, `location` FROM `nanodb`";

$ret_sql_query = $dbconnect->query($sql_query_databases);
if ($ret_sql_query === FALSE) {
    echo "FATAL ERROR: Query to 'jeedom' database failed (databases)\n";
    mysqli_close($dbconnect);
    exit ;
}

$number_of_databases = 0;
$databases_array = array();

/* Loop that iterates over the rows resulting from the SQL query (the remote databases) */
while ( $ret_sql_query_row = $ret_sql_query->fetch_array(MYSQLI_BOTH) ) {

    $number_of_databases += 1;

    // echo "login = <" . $ret_sql_query_row['login'] . ">\n";
    // echo "password = <" . $ret_sql_query_row['password'] . ">\n";
    // echo "address = <" . $ret_sql_query_row['addr'] . ">\n";
    // echo "port = <" . $ret_sql_query_row['port'] . ">\n";
    // echo "path = <" . $ret_sql_query_row['path'] . ">\n";
    // echo "token = <" . $ret_sql_query_row['location'] . ">\n";

   // echo "\n";

    $databases_array[] = array(
        'login' => $ret_sql_query_row['login'],
        'password' => $ret_sql_query_row['password'],
        'address' => $ret_sql_query_row['addr'],
        'port' => intval($ret_sql_query_row['port']),
        'path' =>  $ret_sql_query_row['path'],
        'token' =>  $ret_sql_query_row['location']
    );
}


//echo "---------------------------\n\n";
/******** OBJECTS ********/

/* SQL query to get the objects with parenthood links */
$query = "SELECT o.id, o.name AS name, f.name AS father_name FROM object o LEFT JOIN object f ON o.father_id = f.id";
$res = $dbconnect->query($query);
$array = $res->fetch_all(MYSQLI_ASSOC);

/* 
    unset key 'id'
    set key 'level' with a default value (the good value will be added later)
    set key 'number_of_children' with a default value (the good value will be added later)
    invert keys 'name' and 'level' because I prefer to have 'level' before 'name', otherwise there's no reason to do that
*/
$i = 0;
$number_element_in_array = count($array);
while ($i < $number_element_in_array) {
    unset( $array[$i]['id'] );
    $array[$i]['level'] = 0; 
    $array[$i]['number_of_children'] = 0; /* unset 'id' and set 'level' and 'number_of_children' with a default value */

    /* swap keys 'name' and 'level' */
    $tmp_array = $array[$i];
    $newArray = array();
    foreach ($tmp_array as $key => $value) {
        if ($key == 'name') {
            $newArray['level'] = $tmp_array['level'];
        } elseif ($key == 'level') {
            $newArray['name'] = $tmp_array['name'];
        } else {
            $newArray[$key] = $value;
        }
    }
    $array[$i] = $newArray;

    $i++;
}


//print_r($array);

//echo "STACKOVERFLOOOOOOOW\n";

/* Create a tree array from a flat array (normal array) */
function buildTree(array $elements, $parentId = 0) {
    $branch = array();

    foreach ($elements as $element) {
        if ($element['father_name'] == $parentId) {
            $children = buildTree($elements, $element['name']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
        }
    }

    return $branch;
}

$tree_obj = buildTree($array);

/*
    Unset array element when key 'father_name' is not null.
    This is because the function buildTree() may duplicates elements supposed to be children, and these duplicates 
    are placed as roots of the tree ; so I delete them
*/
$number_element_in_array = count($tree_obj);
$i = 0;
while ($i < $number_element_in_array) {
    if ( $tree_obj[$i]['father_name'] != "" ) {
        unset( $tree_obj[$i] );
    }
    $i++;
}

/* Rearrange the indexes for the objects tree because it may be "broken" due to the unset of duplicates elements earlier */
if ( sort($tree_obj) === FALSE ) {
    echo "FATAL ERROR: sort() failed (\$tree)\n";
    mysqli_close($dbconnect);
    exit ;
}

//  echo "\n PRINT R \n";
//  print_r( $tree_obj );
//  echo " END PRINT R \n";

 /*
    function that unsets 'father_name' and sets the good value for 'level' and 'number_of_children'
 */
function unset_father_name_and_set_level_in_obj_tree(array &$tree_obj, $level_to_insert = 0)
{
    $level_to_insert += 1;
    $nb_elem = count($tree_obj);

    $i = 0;
    while ($i < $nb_elem) {
        $tree_obj[$i]['level'] = $level_to_insert;
        unset($tree_obj[$i]['father_name']);
        if ( isset($tree_obj[$i]['children']) ) {
            $tree_obj[$i]['number_of_children'] = count($tree_obj[$i]['children']);
            unset_father_name_and_set_level_in_obj_tree($tree_obj[$i]['children'], $level_to_insert);
        }

        $i++;
    }
}

unset_father_name_and_set_level_in_obj_tree($tree_obj);

/*
    function that does NOT modify the object tree, just getting infos on it
        display the tree if $print = TRUE ($tab to set the first tabulation offset)
        return an array containg the number of objects and the number of level
*/
function analyze_objects_tree(array $tree_obj, $print = FALSE, $tab = 0, $number_of_objects = 0, $number_of_level = 1)
{
    $ret_obj_level = array();

    $nb_elem = count($tree_obj);

    $i = 0;
    while ($i < $nb_elem) {
        $j = 0; 
        if ($print === TRUE) {
            while ($j < $tab) { 
                echo "    "; 
                $j++; 
            }
            echo $tree_obj[$i]['name'] . "\n";
        }
        $number_of_objects += 1;
        if ( isset($tree_obj[$i]['children']) ) {
            $number_of_level += 1;    
            $ret_obj_level = analyze_objects_tree($tree_obj[$i]['children'], $print, $tab + 1, $number_of_objects, $number_of_level);
            $number_of_objects = $ret_obj_level['number_of_objects'];
        }

        $i++;
    }

    $ret_obj_level['number_of_objects'] = $number_of_objects;
    $ret_obj_level['number_of_level'] = $number_of_level;

    return $ret_obj_level;
}

//echo "---- PRINT MY TREE ----\n";
$rett = analyze_objects_tree($tree_obj, false);
$number_of_objects = $rett['number_of_objects'];
$number_of_level = $rett['number_of_level'];
//echo "PRINT TREE NB OBJ = $number_of_objects // LEVEL = $number_of_level \n";

//echo "TREE OBJ COUNT = " . count($tree_obj) . "\n";

function go_flat(array &$flat_array, array $tree_obj, $count_tree_obj)
{
    for ($i = 0 ; $i < $count_tree_obj ; $i++) {
        $flat_array[] = array('level' => $tree_obj[$i]['level'],
                'name' => $tree_obj[$i]['name'],
                //'father' => $tree_obj[$index]['father'],
                'number_of_children' => $tree_obj[$i]['number_of_children']
            );
        if ($tree_obj[$i]['number_of_children'] > 0) {
            go_flat($flat_array, $tree_obj[$i]['children'], count($tree_obj[$i]['children']));
        }
    }
   

}

$flat_array = array();
go_flat($flat_array, $tree_obj, count($tree_obj));



echo "BEGIN PRINT FLAT ========= \n";
print_r($flat_array);
echo "END PRINT FLAT =========\n";

// SET FATHER
for ($i = 0 ; $i < count($flat_array) ; $i++) {
    $query_get_father_id = 'SELECT `father_id` FROM `object` WHERE `name`="' . $flat_array[$i]['name'] . '"';
    $ret_query = $dbconnect->query($query_get_father_id);
    $father_id = ($ret_query->fetch_array(MYSQLI_BOTH)[0]);

    if ( ! isset($father_id) || empty($father_id) ) {
        $flat_array[$i]['father'] = "";
        continue ;
    }

    $ret_query = $dbconnect->query('SELECT `name` FROM `object` WHERE `id`=' . $father_id);
    $father_name = ($ret_query->fetch_array(MYSQLI_BOTH))[0];

    $flat_array[$i]['father'] = $father_name;
}





//echo "---------------------------\n\n";
/******** JSON ********/
$json_export = array();

/* get value for uid_gateway */
$hostname = gethostname();
if ($hostname === FALSE) {
    echo "FATAL ERROR: gethostname() failed\n";
    mysqli_close($dbconnect);
    exit ;
}

/* Build the final JSON */
$json_export['uid_gateway'] = $hostname;
$json_export['number_of_objects'] = $number_of_objects;
$json_export['number_of_level'] = $number_of_level;
$json_export['objects'] = $flat_array; //$tree_obj;
$json_export['number_of_equipment'] = $number_of_equipment;
$json_export['equipment'] = $equipment_array;
$json_export['number_of_remote_databases'] = $number_of_databases;
$json_export['remote_databases'] = $databases_array;


$final_json = json_encode($json_export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($final_json === FALSE) {
    echo "FATAL ERROR: json_encode() failed\n";
    mysqli_close($dbconnect);
    exit ;
}

echo "\n---- FINAL JSON ----\n";
print_r($final_json);
//var_dump($final_json);

$file_export_json = "/home/pi/tests/import_export/import_export/export_jeedom_config.json"; // A MODIF
$fp_file_export_json = fopen($file_export_json, "w");
if ($fp_file_export_json === FALSE) {
    echo "FATAL ERROR: fopen() failed (" . $file_export_json . ")\n";
    mysqli_close($dbconnect);
    exit ;
}
if ( fwrite($fp_file_export_json, $final_json . "\n") === FALSE ) {
    echo "FATAL ERROR: fwrite() failed to write the json into $file_export_json\n";
    mysqli_close($dbconnect);
    fclose($fp_file_export_json);
    exit ;
}


echo "\n\n---- END\n";

//////// END ////////
mysqli_close($dbconnect);
fclose($fp_file_export_json);
?>

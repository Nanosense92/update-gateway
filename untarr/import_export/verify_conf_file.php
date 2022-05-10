<?php

/*
    Return TRUE if the file is a valid and usable JSON
    Otherwise return FALSE and print on stdin a short message to help understanding why
*/

function verify_conf_file($file)
{
    /* read the file and put its content into a string */
    $file_str = file_get_contents($file);
    if ($file_str === FALSE) {
        echo "IMPORT FATAL ERROR : file_get_contents() failed with file '$file' ... exiting\n";
        return FALSE ;
    }

    /******** TEST IF FILE IS A VALID JSON ********/

    /* regex found on stackoverflow, to check if a string matches JSON format */
    $regex_delamorkitu = '
        /
        (?(DEFINE)
        (?<number>   -? (?= [1-9]|0(?!\d) ) \d+ (\.\d+)? ([eE] [+-]? \d+)? )    
        (?<boolean>   true | false | null )
        (?<string>    " ([^"\\\\]* | \\\\ ["\\\\bfnrt\/] | \\\\ u [0-9a-f]{4} )* " )
        (?<array>     \[  (?:  (?&json)  (?: , (?&json)  )*  )?  \s* \] )
        (?<pair>      \s* (?&string) \s* : (?&json)  )
        (?<object>    \{  (?:  (?&pair)  (?: , (?&pair)  )*  )?  \s* \} )
        (?<json>   \s* (?: (?&number) | (?&boolean) | (?&string) | (?&array) | (?&object) ) \s* )
        )
        \A (?&json) \Z
        /six   
    ';

    /* test the regex */
    if ( preg_match($regex_delamorkitu, $file_str) !== 1 ) {
        echo "JSON NOT VALID : did not pass the regex test\n";
        return FALSE;
    }

    /* test with json_decode() */
    $json = json_decode($file_str, $assoc = TRUE);
    if ( $json === NULL ) {
        echo "JSON NOT VALID : json_decode() returned NULL\n";
        return FALSE ;
    }
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        echo "JSON NOT VALID : json_last_error() shows an error for json_decode()\n";
        return FALSE ;
   	}
   
   	/* ok if the code gets here, the file must be a valid json */


	/******** VERIFY IF ALL THE FIELD ARE SET WITH A VALID VALUE ********/
	   
	/*
		Verify that all the keys are set in the first level of the "tree",
		meaning no check is done inside the sub-arrays (it's done right after).
		The type of the value is checked ;
		The length of strings must be greater than 0 ;
		'uid_gateway' must be the hostname ;
	*/

	/* check 'uid_gateway' */
	if ( isset($json['uid_gateway']) === FALSE ) {
		echo "JSON NOT VALID : key 'uid_gateway' is not set\n";
		return FALSE;
	}
	if ( is_string($json['uid_gateway']) === FALSE ) {
		echo "JSON NOT VALID : key 'uid_gateway' is not string type\n";
		return FALSE;
	}
	if ( strlen($json['uid_gateway']) === 0 ) {
		echo "JSON NOT VALID : key 'uid_gateway' has 0 length\n";
		return FALSE;
	}
	$hostname = gethostname();
	if ($hostname === FALSE) {
		echo "FATAL ERROR : gethostname() failed\n";
		return FALSE;
	}
	if ($hostname !== $json['uid_gateway']) {
		echo "JSON NOT VALID : key 'uid_gateway' is not the hostname\n";
		return FALSE;
	}

	/* check 'number_of_objects' */
	if ( isset($json['number_of_objects']) === FALSE ) {
		echo "JSON NOT VALID : key 'number_of_objects' is not set\n";
		return FALSE;
	}
	if ( is_int($json['number_of_objects']) === FALSE ) {
		echo "JSON NOT VALID : key 'number_of_objects' is not integer type\n";
		return FALSE;
	}

	/* check 'number_of_level' */
	if ( isset($json['number_of_level']) === FALSE ) {
		echo "JSON NOT VALID : key 'number_of_level' is not set\n";
		return FALSE;
	}
	if ( is_int($json['number_of_level']) === FALSE ) {
		echo "JSON NOT VALID : key 'number_of_level' is not integer type\n";
		return FALSE;
	}

	/* check 'objects' */
	if ( isset($json['objects']) === FALSE ) {
		echo "JSON NOT VALID : key '' is not set\n";
		return FALSE;
	}
	if ( is_array($json['objects']) === FALSE ) {
		echo "JSON NOT VALID : key 'objects' is not array type\n";
		return FALSE;
	}

	/* check 'number_of_equipment' */
	if ( isset($json['number_of_equipment']) === FALSE ) {
		echo "JSON NOT VALID : key 'number_of_equipment' is not set\n";
		return FALSE;
	}
	if ( is_int($json['number_of_equipment']) === FALSE ) {
		echo "JSON NOT VALID : key 'number_of_equipment' is not integer type\n";
		return FALSE;
	}

	/* check 'equipment' */
	if ( isset($json['equipment']) === FALSE ) {
		echo "JSON NOT VALID : key 'equipment' is not set\n";
		return FALSE;
	}
	if ( is_array($json['equipment']) === FALSE ) {
		echo "JSON NOT VALID : key 'equipment' is not integer type\n";
		return FALSE;
	}

	/* check 'number_of_remote_databases' */
	if ( isset($json['number_of_remote_databases']) === FALSE ) {
		echo "JSON NOT VALID : key 'number_of_remote_databases' is not set\n";
		return FALSE;
	}
	if ( is_int($json['number_of_remote_databases']) === FALSE ) {
		echo "JSON NOT VALID : key 'number_of_remote_databases' is not integer type\n";
		return FALSE;
	}

	/* check 'remote_databases' */
	if ( isset($json['remote_databases']) === FALSE ) {
		echo "JSON NOT VALID : key 'remote_databases' is not set\n";
		return FALSE;
	}
	if ( is_array($json['remote_databases']) === FALSE ) {
		echo "JSON NOT VALID : key 'remote_databases' is not array type\n";
		return FALSE;
	}


	/*
		Verify that all the necessary keys are set with a valid value (the type is checked, not the real value) in the sub-array 'objects' ;
		The 'number_of_children' is verified according to the the "real" number of children described ;
		The 'level' is verified according to the "real" level described ;
		The "real" number of objects described is checked according to the key 'number_of_objects' ;
	*/
if ( function_exists('check_validity_of_subarray_objects') === FALSE ) /* the function below is inside another function, this check is necessary if the containing function is called multiple times */
{
	function check_validity_of_subarray_objects(array $array, $key_number_of_objects, &$max_level_found, $real_level = 1, &$real_number_of_objects = 0)
	{
		$nb_elem = count($array);
		if ($nb_elem !== $key_number_of_objects) {
			echo "JSON NOT VALID : key 'number_of_object' does not correspond to what is described in 'object' array\n";
			return FALSE;
		}
	//	echo "nb elem = " . $nb_elem . " / real nb obj = " . $real_number_of_objects . "\n" ;
		$real_number_of_objects += $nb_elem;
	
		for ($i = 0 ; $i < $nb_elem ; $i++) 
		{
			if ( isset($array[$i]['level']) === FALSE ) {
				echo "JSON NOT VALID : key 'level' (in 'objects' sub-array) is not set\n";
				return FALSE;
			}
			if ( is_int($array[$i]['level']) === FALSE ) {
				echo "JSON NOT VALID : key 'level' (in 'objects' sub-array) is not an integer type\n";
				return FALSE;
			}
			//if ( $array[$i]['level'] !== $real_level ) {
			//	echo "JSON NOT VALID : key 'level' (in 'objects' sub-array) has not the good value\n";
			//	return FALSE;
			//}
			/* $max_level_found is taking the maximum level value found, and later it will be compared to the value of the key 'number_of_level' from the json */
			//if ($max_level_found < $real_level) {
			//	$max_level_found = $real_level;
			//}

			if ($max_level_found < $array[$i]['level']) {
				$max_level_found = $array[$i]['level'];
			}

			if ( isset($array[$i]['name']) === FALSE ) {
				echo "JSON NOT VALID : key 'name' (in 'objects' sub-array) is not set\n";
				return FALSE;
			}
			if ( is_string($array[$i]['name']) === FALSE ) {
				echo "JSON NOT VALID : key 'name' (in 'objects' sub-array) is not a string type\n";
				return FALSE;
			}
			if ( strlen($array[$i]['name']) === 0 ) {
				echo "JSON NOT VALID : key 'name' (in 'objects' sub-array) is 0 length\n";
				return FALSE;
			}

			if ( isset($array[$i]['father']) === FALSE ) {
				echo "JSON NOT VALID : key 'father' (in 'objects' sub-array) is not set\n";
				return FALSE;
			}
			if ( is_string($array[$i]['father']) === FALSE ) {
				echo "JSON NOT VALID : key 'father' (in 'objects' sub-array) is not a string type\n";
				return FALSE;
			}

			if ( strlen($array[$i]['father']) !== 0 && $array[$i]['level'] === 1 ) {
				echo "JSON NOT VALID : key 'level' (in 'objects' sub-array) is set to 1 but this object has a father\n";
				return FALSE;
			}

			if ( isset($array[$i]['number_of_children']) === FALSE ) {
				echo "JSON NOT VALID : key 'number_of_children' (in 'objects' sub-array) is not set\n";
				return FALSE;
			}
			if ( is_int($array[$i]['number_of_children']) === FALSE ) {
				echo "JSON NOT VALID : key 'number_of_children' (in 'objects' sub-array) is not an integer type\n";
				return FALSE;
			}

			//if ( isset($array[$i]['children']) === TRUE ) {	/* key 'children' exists and its value is not null */
			//	if ( is_array($array[$i]['children']) === FALSE ) {
			//		echo "JSON NOT VALID : key 'children' (in 'objects' sub-array) is not an array type\n";
			//		return FALSE;
			//	}
				
			//	if ( count($array[$i]['children']) !== $array[$i]['number_of_children'] ) {
			//		echo "JSON NOT VALID : for object " . $array[$i]['name'] . ", key 'number_of_children' has not a good value compared to the \"real\" number of children\n";
			//		return FALSE;
			//	}

				//if ( check_validity_of_subarray_objects($array[$i]['children'], $key_number_of_objects, $max_level_found, $real_level + 1, $real_number_of_objects) === FALSE ) {
				//	return FALSE;
				//}
			//}

			//else {	/* key 'children' does not exist or its value is null */
			//	if ($array[$i]['number_of_children'] !== 0) {
			//		echo "JSON NOT VALID : key 'children' is not set, but key 'number_of_children' has a value different than 0\n";
			//		return FALSE;
			//	}
			//}

		} // end for ($i = 0 ; $i < $nb_elem ; $i++) 
	
		/* if $real_level is 1 here, it means this function is finishing and returning TRUE (not "unstacking" recursion),
			so the "real" number of objects can be compared with the key 'number_of_objects */
		//if ($real_level === 1 && $real_number_of_objects !== $key_number_of_objects) {
		//	echo "JSON NOT VALID : the \"real\" number of objects described does not correspond to the key 'number_of_objects'\n";
		//	return FALSE;
		//}

		return TRUE;
	} // end function check_validity_of_subarray_objects()
} // end if() function_exists()

	$max_level_found = 0;
	if ( check_validity_of_subarray_objects($json['objects'], $json['number_of_objects'], $max_level_found) === FALSE ) {
		return FALSE;
	}
	if ($max_level_found !== $json['number_of_level']) {
		echo "JSON NOT VALID : key 'number_of_level' does not correspond to what is described in the 'object' array\n";
		return FALSE;
	}

	/* $max_level_found is the maximum level value found in the subarray 'objects' ;
		if $max_level_found is different from the value 'number_of_level' from the json, then the json is invalid */
	//if ($max_level_found !== $json['number_of_level']) {
	//	echo "JSON NOT VALID : key 'number_of_level' doesn't have a good value according to reality described\n";
	//	echo "max lvl found = $max_level_found\n";
	//	return FALSE;
	//}


	/* Checking the validity of the subarray 'equipment'
		The values must be set and have the good type (the value itself is not checked, except for 'number_of_equipment') */
	if ( $json['number_of_equipment'] !== count($json['equipment']) ) {
		echo "JSON NOT VALID : key 'number_of_equipment' doesn't have a good value according to reality described\n";
		return FALSE;
	}

	/* Looping through the subarray 'equipment' to verify that each value is set with the appropriate type */
	for ($i = 0 ; $i < $json['number_of_equipment'] ; $i++) 
	{
		/* check 'probe_model' */
		if ( isset($json['equipment'][$i]['probe_model']) === FALSE ) {
			echo "JSON NOT VALID : key 'probe_model' (in sub-array 'equipment') is not set\n";
			return FALSE;
		}
		if ( is_string($json['equipment'][$i]['probe_model']) === FALSE ) {
			echo "JSON NOT VALID : key 'probe_model' (in sub-array 'equipment') is not string type\n";
			return FALSE;
		}
		if ( strlen($json['equipment'][$i]['probe_model']) === 0 ) {
			echo "JSON NOT VALID : key 'probe_model' (in sub-array 'equipment') has 0 length\n";
			return FALSE;
		}

		if ($json['equipment'][$i]['probe_model'] !== "E4000" && $json['equipment'][$i]['probe_model'] !== "P4000"
		&& $json['equipment'][$i]['probe_model'] !== "QAA" && $json['equipment'][$i]['probe_model'] !== "EP5000"
		&& $json['equipment'][$i]['probe_model'] !== "DOOR" && $json['equipment'][$i]['probe_model'] !== "WINDOW"
		&& $json['equipment'][$i]['probe_model'] !== "OCCUPANCY") {
			echo "JSON NOT VALID : key 'probe_model' (in sub-array 'equipment') must be \"E4000\" or \"P4000\" or \"QAA\" or \"EP5000\" or DOOR/WINDOW or OCCUPANCY\n";
			return FALSE;
		}

		/* check 'alias' */
		if ( isset($json['equipment'][$i]['alias']) === FALSE ) {
			echo "JSON NOT VALID : key 'alias' (in sub-array 'equipment') is not set\n";
			return FALSE;
		}
		if ( is_string($json['equipment'][$i]['alias']) === FALSE ) {
			echo "JSON NOT VALID : key 'alias' (in sub-array 'equipment') is not string type\n";
			return FALSE;
		}
		if ( strlen($json['equipment'][$i]['alias']) === 0 ) {
			echo "JSON NOT VALID : key 'alias' (in sub-array 'equipment') has 0 length\n";
			return FALSE;
		}

		/* check 'id' */
		if ( isset($json['equipment'][$i]['id']) === FALSE ) {
			echo "JSON NOT VALID : key 'id' (in sub-array 'equipment') is not set\n";
			return FALSE;
		}
		if ( is_string($json['equipment'][$i]['id']) === FALSE ) {
			echo "JSON NOT VALID : key 'id' (in sub-array 'equipment') is not string type\n";
			return FALSE;
		}

		/* check 'object' */
		if ( isset($json['equipment'][$i]['object']) === FALSE ) {
			echo "JSON NOT VALID : key 'object' (in sub-array 'equipment') is not set\n";
			return FALSE;
		}
		if ( is_string($json['equipment'][$i]['object']) === FALSE ) {
			echo "JSON NOT VALID : key 'object' (in sub-array 'equipment') is not string type\n";
			return FALSE;
		}

		/* check 'eep' */
		if ( isset($json['equipment'][$i]['eep']) === FALSE ) {
			echo "JSON NOT VALID : key 'eep' (in sub-array 'equipment') is not set\n";
			return FALSE;
		}
		if ( is_string($json['equipment'][$i]['eep']) === FALSE ) {
			echo "JSON NOT VALID : key 'eep' (in sub-array 'equipment') is not string type\n";
			return FALSE;
		}
	} // end for ($i = 0 ; $i < $json['number_of_equipment'] ; $i++)
	
	
	/* Checking the validity of the subarray 'remote_databases'
		The values must be set and have the good type (the value itself is not checked, except for 'number_of_equipment') */
		if ( $json['number_of_remote_databases'] !== count($json['remote_databases']) ) {
			echo "JSON NOT VALID : key 'number_of_remote_databases' doesn't have a good value according to reality described\n";
			return FALSE;
		}
	
		/* Looping through the subarray 'remote_databases' to verify that each value is set with the appropriate type */
		for ($i = 0 ; $i < $json['number_of_remote_databases'] ; $i++) 
		{
			/* check 'login' */
			if ( isset($json['remote_databases'][$i]['login']) === FALSE ) {
				echo "JSON NOT VALID : key 'login' (in sub-array 'remote_databases') is not set\n";
				return FALSE;
			}
			if ( is_string($json['remote_databases'][$i]['login']) === FALSE ) {
				echo "JSON NOT VALID : key 'login' (in sub-array 'remote_databases') is not string type\n";
				return FALSE;
			}
	
			/* check 'password' */
			if ( isset($json['remote_databases'][$i]['password']) === FALSE ) {
				echo "JSON NOT VALID : key 'password' (in sub-array 'remote_databases') is not set\n";
				return FALSE;
			}
			if ( is_string($json['remote_databases'][$i]['password']) === FALSE ) {
				echo "JSON NOT VALID : key 'password' (in sub-array 'remote_databases') is not string type\n";
				return FALSE;
			}
	
			/* check 'address' */
			if ( isset($json['remote_databases'][$i]['address']) === FALSE ) {
				echo "JSON NOT VALID : key 'address' (in sub-array 'remote_databases') is not set\n";
				return FALSE;
			}
			if ( is_string($json['remote_databases'][$i]['address']) === FALSE ) {
				echo "JSON NOT VALID : key 'address' (in sub-array 'remote_databases') is not string type\n";
				return FALSE;
			}
	
			/* check 'port' */
			if ( isset($json['remote_databases'][$i]['port']) === FALSE ) {
				echo "JSON NOT VALID : key 'port' (in sub-array 'remote_databases') is not set\n";
				return FALSE;
			}
			if ( is_int($json['remote_databases'][$i]['port']) === FALSE ) {
				echo "JSON NOT VALID : key 'port' (in sub-array 'remote_databases') is not integer type\n";
				return FALSE;
			}

			/* check 'path' */
			if ( isset($json['remote_databases'][$i]['path']) === FALSE ) {
				echo "JSON NOT VALID : key 'path' (in sub-array 'remote_databases') is not set\n";
				return FALSE;
			}
			if ( is_string($json['remote_databases'][$i]['path']) === FALSE ) {
				echo "JSON NOT VALID : key 'path' (in sub-array 'remote_databases') is not string type\n";
				return FALSE;
			}

			/* check 'token' */
			if ( isset($json['remote_databases'][$i]['token']) === FALSE ) {
				echo "JSON NOT VALID : key 'token' (in sub-array 'remote_databases') is not set\n";
				return FALSE;
			}
			if ( is_string($json['remote_databases'][$i]['token']) === FALSE ) {
				echo "JSON NOT VALID : key 'token' (in sub-array 'remote_databases') is not string type\n";
				return FALSE;
			}

		} // end for ($i = 0 ; $i < $json['number_of_remote_databases'] ; $i++)



   return TRUE; /* conf file is valid */
} // end function verify_conf_file()



?>

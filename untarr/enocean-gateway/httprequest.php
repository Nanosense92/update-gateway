<?php
/*
 * FILE : httprequest.php
 */

/* 
 *
 * Parameters:
 *
 * $dbconnect = mysql connection a database
 * $log = main log file
 * $data = the data to send
 * $alias = the current equipment alias
 * $pollutant = the name of the current pollutant
 * $errlog = the error log file
 *
 */
function http_request($dbconnect, $log, $data, $alias, $pollutant, $errlog)
{ 
    // get all the informations about the URL where the data will be sent
    $http_query = $dbconnect->query('SELECT * FROM nanodb');
    while ($http_row = $http_query->fetch_array(MYSQLI_BOTH)) {

        $token = $http_row['location'];
        $url = $http_row['addr'];
        $login = $http_row['login'];
        $pass = $http_row['password']; //echo "PASSWORD = |$pass|\n\n\n";

        // if the port is set, add it at the end of the URL with a ':' before
        if ($http_row['port'] != NULL)
            $url = $url . ':' . $http_row['port'];

        // add the path at the end of the URL with a '/' if it is missing
        if ($http_row['path'][0] != '/')
            $url = $url . '/' . $http_row['path'];
        else
            $url = $url . $http_row['path'];

        fwrite($log, $url . "\n\n");

        $ch = curl_init($url);

        /*
         *  Set the HTTP Post request with CURL
         */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Token: ' . $token,
        )); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERPWD, "$login:$pass");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true); //debug
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ( $http_row['port'] == 443 || strpos($url, "https://") === true )
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            require_once 'find_cert_local_path.php';
            $cert_local_path = find_cert_local_path($http_row['addr']);
         //   echo "RETT = $cert_local_path\n";
            curl_setopt($ch, CURLOPT_CAINFO, $cert_local_path);
        }
        
        $curl_res = curl_exec($ch);

        /* DEBUG */
        $header_sent = curl_getinfo($ch, CURLINFO_HEADER_OUT );
        // echo "\nDEBUG ** CURL GET INFO ---- BEGIN ----\n";
        //  var_dump(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        // echo "DEBUG ** CURL GET INFO ----  END  ----\n";
        
    
        // echo "\nDEBUG ** HEADER SENT ---- BEGIN ----\n";
        // print_r($header_sent);
        // echo "DEBUG ** HEADER SENT ----  END  ----\n";
        
        //if ( true && (strpos($data, 'big_presence') !== FALSE 
        //|| strpos($data, 'beige') !== FALSE) )
        if (true)
        {
            echo "\nDEBUG ** JSON ---- BEGIN ----\n";
            echo $data . "\n";
            echo "DEBUG ** JSON ----  END  ----\n";
        }

		// echo "=== ";
		// var_dump($data['datastreams']);
		// echo "\n";
            
        

            //var_dump($curl_res);
        /* DEBUG */

        //fwrite($log, $curl_res . "\n");

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      //  echo "\nDEBUG ** HTTPCODE = " . $httpcode . "\n";
        if ($httpcode >= 400){
            $error_msg = date('Y-m-d H:i:s') . ' '
                . $alias . ' ' . $pollutant . ' '
                . $url . ' ' . $httpcode . "\n";
            fwrite($errlog, $error_msg);
        }
        if (curl_errno($ch)){
            echo 'CURL error: '. curl_error($ch) . "\n";
        }

        curl_close($ch);
   } // end while()
} // end function
?>

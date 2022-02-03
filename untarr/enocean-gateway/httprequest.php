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
    $push_infos_array = file("/var/www/html/nanosense/pushtocloud.conf", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($push_infos_array === false) {
        echo "FATAL ERROR: failed to open file pushtocloud.conf (3)\n";
        exit ;
    }

    //$http_query = $dbconnect->query('SELECT * FROM nanodb');
    $nb_lines = count($push_infos_array);
    //while ($http_row = $http_query->fetch_array(MYSQLI_BOTH)) {
    for ($i = 0 ; $i < $nb_lines ; $i++) {
        $exploded = explode(' ', $push_infos_array[$i]);
        // retirer les quotes
        for ($j = 0 ; $j < count($exploded) ; $j++) {
            $exploded[$j] = trim($exploded[$j], "'");
        }
        // flemme de modifier tout le code qui suit du coup je recréé http_row[]
        $http_row = array('login' => $exploded[0],
                          'password' => $exploded[1],
                          'addr' => $exploded[2],
                          'port' => $exploded[3],
                          'path' => $exploded[4],
                          'location' => $exploded[5]
                        );


        $token = $http_row['location'];
        $url = $http_row['addr'];
        $login = $http_row['login'];
        $pass = $http_row['password']; //echo "PASSWORD = |$pass|\n\n\n";

        $url_without_ending_slash = $url;

        // retirer le slash a la fin de addr si il y en a un
        if ( $url[strlen($url) - 1] === '/' ) {
            //echo ">>> ENDING SLASH\n";
            $url = substr($url, 0, -1);
            $url_without_ending_slash = $url;
        }


        if ( strpos($url, "ftp") !== false ) {

            $protocol = "FTP";
            if ( strpos($url, "sftp") !== false ) {
                $protocol = "SFTP";
            }
            if ( strpos($url, "ftps") !== false ) {
                $protocol = "FTPS";
            }

            $ftp_url = $url_without_ending_slash;

            if ($http_row['path'] != NULL) {
                if ($http_row['path'][0] != '/') {
                    $ftp_url = $url_without_ending_slash . '/';
                }
                $ftp_url = $ftp_url . $http_row['path'];
                if ( $ftp_url[strlen($ftp_url) - 1] !== '/' ) {
                    $ftp_url = $ftp_url . '/';
                }
            }
            else {
                $ftp_url = $ftp_url . '/';
            }
            
            $ftp_port = 21;
            if ($protocol === "SFTP") {
                $ftp_port = 22;
            }
            if ($http_row['port'] != NULL) {
                $ftp_port = $http_row['port'];
            }

            $user = "";
            $passwd = "";
            $user_and_passwd = NULL;
            if ($http_row['login'] != NULL) {
                $user = $http_row['login'];
                if ($http_row['password'] != NULL) {
                    $passwd = $http_row['password'];
                    $user_and_passwd = $user . ':' . $passwd;
                }
            }
            
            ftp_request($data, $ftp_url, $ftp_port, $alias, $pollutant, $user_and_passwd, $protocol);
            continue ;
        } // if() ftp


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

        if (true) {
            echo $url . "\n";
        }

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
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            require_once 'find_cert_local_path.php';
            $cert_local_path = find_cert_local_path($url_without_ending_slash);
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
        if ( curl_errno($ch) ) {
            echo date('Y-m-d H:i:s') . ' CURL error: ' . curl_error($ch) . "\n";
            fwrite($errlog,  date('Y-m-d H:i:s') . ' CURL error: ' . curl_error($ch) . "\n");
        }

        curl_close($ch);
   } // end while()
} // end function http_request()





function ftp_request($data, $ftp_url, $ftp_port, $alias, $pollutant, $user_and_passwd, $protocol)
{
    echo "FUNC FTP REQUEST()\nprotocol = $protocol\nURL = $ftp_url\nport = $ftp_port\nalias = $alias\nuserpasswd = $user_and_passwd\n";
    // open tmp file
    // write json into tmp file
    // upload tmp file

    $ftp_tmp_file = "/tmp/tmp_ftp.json";
    $fp_ftp_tmp_file = fopen($ftp_tmp_file, 'w');
    if ($fp_ftp_tmp_file === FALSE) {
        echo "FATAL ERROR FOPEN FAILED (w)\n";
        return ;
    }
    $ret = fwrite($fp_ftp_tmp_file, $data);  //echo "WRITE RETURNED $ret\n";
    fclose($fp_ftp_tmp_file);

    chmod($ftp_tmp_file, 777);

    clearstatcache(true, $ftp_tmp_file);

    $fp_ftp_tmp_file = fopen($ftp_tmp_file, 'r');
    if ($fp_ftp_tmp_file === FALSE) {
        echo "FATAL ERROR FOPEN FAILED (r)\n";
        return ;
    }


    $outfile = "nanosense__" . $alias . "__" . $pollutant . "__" . date("YmdHis", time());
    echo "FTP OUTFILE = '$outfile'\n";


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ftp_url . $outfile);
    curl_setopt($ch, CURLOPT_PORT, $ftp_port);
    if ($user_and_passwd !== NULL) {
        curl_setopt($ch, CURLOPT_USERPWD, $user_and_passwd);
    }
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    if ($protocol === "SFTP") {
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
    }
    curl_setopt($ch, CURLOPT_INFILE, $fp_ftp_tmp_file);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($ftp_tmp_file));
    if ($protocol === "FTPS") {
        curl_setopt($ch, CURLOPT_FTP_SSL, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
    }
    curl_exec ($ch);
    $error_no = curl_errno($ch);
    if ($error_no) {
        echo 'File Upload CURL error: '. curl_error($ch) . "\n";
    } 
    else {
        echo "File uploaded succesfully\n";
    }

    curl_close ($ch);
    fclose($fp_ftp_tmp_file);


} // end function ftp_request()


?>

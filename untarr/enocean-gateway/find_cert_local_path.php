<?php

// EXAMPLE CERT FILE LOCATION AND NAME : /home/pi/.certs/nsapi-staging.pando2.fr.cert
// EXAMPLE VALIDITY DATE FILE LOCATION AND NAME : /home/pi/.certs/nsapi-staging.pando2.fr.valid

/* Returns the cert file path (string)
    If the cert file does not exist, it's grabbed from the internet, 
    saved into the proper location and its validation date is also stored. 
    If the cert file exists but its validation date is obsolete, 
    a new cert is grabbed from the internet and the new validation date replaces the old one.

    If an error occurs, returns nothing.
*/

function find_cert_local_path($addr)
{
    /* je me passe de "https://" */
    $addr = substr($addr, 8);
    //echo $addr  . "\n" ;

    /* cert files local location */
    $cert_dir = "/home/pi/.certs/";

    /* if file already exists, its validation date is checked */
    if ( file_exists($cert_dir . $addr . ".cert") === TRUE )
    {
        if ( cert_validation_date_is_ok($cert_dir, $addr) === false ) {
            /* Delete the existing file because its validation date is not valid */
            unlink($cert_dir . $addr . ".cert");
            /* Delete the validation date file because it's out of date */
            unlink($cert_dir . $addr . ".valid");
            /* Grab a new cert from the net, and its validation date ; both are stored in files */
            if ( grab_cert_from_net_and_store_it_into_gateway($addr, $cert_dir) === FALSE ) {
                echo "FUNC GRAB RETURNED FALSE!!!\n";
                return ;
            }
        }

        /* return the cert file path */
        $ret_cert_local_path = $cert_dir . $addr . ".cert";
        return $ret_cert_local_path;
    }

echo "FILE DOES NOT EXIST AAAAAAAAAH\n";

    /* If the directory for cert files does no exist, it is created */
    if ( file_exists($cert_dir) === FALSE ) 
        if ( mkdir($cert_dir, 0755) === FALSE )
            return ;

    /* read the function's name */
    if ( grab_cert_from_net_and_store_it_into_gateway($addr, $cert_dir) === FALSE ) {
        echo "FUNC GRAB RETURNED FALSE\n";
        return ;
    }

    $ret_cert_local_path = $cert_dir . $addr . ".cert";
    return $ret_cert_local_path;
}


/* returns TRUE on success, otherwise FALSE */
function grab_cert_from_net_and_store_it_into_gateway($addr, $cert_dir)
{
    /* grab cert from net 
        example shell command : 
        echo | openssl s_client -servername "https://nsapi-staging.pando2.fr" \
        -connect "nsapi-staging.pando2.fr:443" |  sed -ne '/-BEGIN CERTIFICATE-/,/-END CERTIFICATE-/p' \
        > certificate.crt
    */
    $exec_cmd = "echo | openssl s_client -servername https://"
        . $addr 
        . " -connect " /* do not put https:// with this connect option */
        . $addr
        . ":443 2>/dev/null | sed -ne '/-BEGIN CERTIFICATE-/,/-END CERTIFICATE-/p' > " // 443 2>/dev/null
        . $cert_dir . $addr . ".cert";

    $exec_output = array();
echo "EXEC OPENSSL\n";
    exec($exec_cmd, $exec_output, $exec_ret);
    
    if ($exec_ret !== 0) {
echo "EXEC DID NOT RETURN 0    OOUUUUUH  ret = $exec_ret\n";
        return FALSE;
    }

    /* check if the file now really exists */
    $cert_local_path = $cert_dir . $addr . ".cert";
    if ( file_exists($cert_local_path) === FALSE )
        return FALSE;

    /* be sure the file is Unix formatted */
    exec("dos2unix $cert_local_path 1>/dev/null 2>/dev/null");

    if ( get_validation_date_and_store_it($cert_dir, $addr) === FALSE )
        return FALSE;

    return TRUE;
}


function cert_validation_date_is_ok($cert_dir, $addr) 
{
    $cert_date_str = file_get_contents($cert_dir . $addr . ".valid");

    $obj_cert_date = new DateTime($cert_date_str);
    $obj_date_now = new DateTime( date('M j g:i:s Y') );

    if ($obj_cert_date >= $obj_date_now) {
      //  echo "OK DATE VALID\n";
        return TRUE;
    }

    echo "NOOOON DATE PAS VALIDE\n";
    return FALSE;

    //echo date('M j g:i:s Y') . "\n";
}


function get_validation_date_and_store_it($cert_dir, $addr)
{
    /* grab cert date from net 
        example shell command : 
        echo | openssl s_client -servername nsapi-staging.pando2.fr \
        -connect nsapi-staging.pando2.fr:443 2>/dev/null \
        | openssl x509 -noout -dates \
        | grep  "notAfter" \
        | cut -d '=' -f 2 | cut -d ' ' -f -5 \
        > file.valid
    */
    /* example output for this command : "Nov  6 12:00:00 2020" */
    $exec_cmd = "echo | openssl s_client -servername " . $addr. " "
        . "-connect " . $addr . ":443 2>/dev/null "
        . "| openssl x509 -noout -dates "
        . "| grep  'notAfter' "
        . "| cut -d '=' -f 2 | cut -d ' ' -f -5 "
        . "> " . $cert_dir . $addr . ".valid";

    $exec_output = array();
echo "EXEC OPENSSL\n";
    exec($exec_cmd, $exec_output, $exec_ret);
    
    if ($exec_ret !== 0) {
echo "EXEC DID NOT RETURN 0    OOUUUUUH (VALID DATE) ret = $exec_ret\n";
        return FALSE;
    }

    return TRUE;
}

?>

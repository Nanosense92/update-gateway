<?php
   
    $ret = chdir("/home/pi/modbus-gateway");
    echo "CHDIR ret = $ret\n";
    echo getcwd() . "\n";
   //$output = shell_exec("python3 /home/pi/modbus-gateway/main.py --cache 0000");
    /*
    $output = array();
    $ret = exec("sudo /usr/bin/python3.5 /home/pi/modbus-gateway/main.py --cache 0000 2>&1", $output, $return_value);

    echo "JAI FINI\nret = $ret\nreturn_value = $return_value\nfin";

    echo "output = \n";
    var_dump($output);

    phpinfo();
    */

    
    $x = parse_ini_file("/home/pi/modbus-gateway/modbus__cache/0000.ini", true);
    //var_dump($x);
    //echo("\n\n");
    echo($x['device5']['type']);

/*
    foreach($x as $key=>$value) {
        echo("$key -> $value<br/>");

        echo '<button type="button" onclick=spawn_button(' . "machine" . ')>machine</button>';        
    }
*/
    //var_dump($x['device5']);


?>

<!-- <button type="button" onclick=generate_buttons()>Launch script!</button> -->

<script>
/*
function spawn_button(name) {
        // 1. Create the button
        var button = document.createElement("button");
        button.innerHTML = name;

        // 2. Append somewhere
        var body = document.getElementsByTagName("body")[0];
        body.appendChild(button);

        // 3. Add event handler
        button.addEventListener ("click", function() {
        alert("did something");
        });
}
*/
</script>


<script>
function generate_buttons() {
    <?php
    
    foreach($x as $key=>$value) {
        
        echo '<button type="button" onclick=spawn_button(' . "machine" . ')>machine</button>';        
    }
    ?>
}
</script>


<!--div onclick="click()", style="width:200px;height:200px;border:1px solid #000;">This is a rectangle!</div-->
 



<?php
require_once "/home/pi/enocean-gateway/get_database_password.php";

if (isset($_POST['db_id'])){
    $db_id = $_POST['db_id'];

    $db = mysqli_connect('localhost', 'jeedom', $jeedom_db_passwd, 'jeedom');
    if ($db->connect_errno){
        echo 'connection to db failed';
        exit;
    }

    $nanodb_table = 'SELECT login, password, addr, port, path, location FROM nanodb WHERE id = ' . $db_id;
    $dbres = $db->query($nanodb_table);
    $row = $dbres->fetch_array(MYSQLI_BOTH);
    $login = $row[0];
    $pass = $row[1];
    $addr = $row[2];
    $port = $row[3];
    $path = $row[4];
    $loc = $row[5];
    
    $db->close();
}
include('modifysql.php')
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Modify an existing database</title>
        <link rel="stylesheet" type="text/css" href="database.css">
        <meta charset="UTF-8">
        <div style="padding-bottom: 20px">
            <div class="topleft">
                <a href="main.php">
                    <img src="logo-nano.png">
                </a>
            </div>
            <center>
                <a href="modify.php">
                    <img src="nano-header.png" width="322" height="63">
                </a>
            </center>
        </div>
    </head>
    <body>
        <form method="post" action="modify.php">
            <div class="container">
                <h1>Database Settings</h1>
                <hr>

                <label for="login"><b>Login (optional)</b></label>
                <input type="text" placeholder="Enter login" name="log" value="<?php echo $login?>" optional>
                
                <label for="psw"><b>Password (optional)</b></label>
                <input type="password" placeholder="Enter password" name="psw" value="<?php echo $pass?>" optional>

                <label for="addr"><b>Website address (required)</b></label>
                <input type="url" placeholder="Enter address" name="addr" value="<?php echo $addr?>" required>

                <label for="port"><b>Port (required)</b></label>
                <input type="number" min="0" max="65535" placeholder="Enter port" name="port" value="<?php echo $port?>" required>

                <label for="path"><b>Api path (optional)</b></label>
                <input type="text" placeholder="Enter path" name="path" value="<?php echo $path?>" optional>

                <label for="key"><b>EnOcean box location/key (optional)</b></label>
                <input type="text" placeholder="Enter key" name="key" value="<?php echo $loc?>" optional>
                
                <input type="hidden" name="db_id" value="<?php echo $db_id?>"> 
                <div class="clearfix">
                    <button onclick="window.location.href='main.php'" type="button" class="cancelbtn">Cancel</button>
                    <button type="submit" class="signupbtn" name="reg_db">Modify</button>
                </div>
            </div>
        </form>
    </body>
</html>

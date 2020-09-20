<?php include('pushtosql.php') ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Register a new database</title>
        <link rel="stylesheet" type="text/css" href="database.css">
        <meta charset="UTF-8">
        <div style="padding-bottom: 20px">
            <div class="topleft">
                <a href="main.php">
                    <img src="logo-nano.png">
                </a>
            </div>
            <center>
                <a href="register.php">
                    <img src="nano-header.png" width="322" height="63">
                </a>
            </center>
        </div>
    </head>
    <body>
        <form method="post" action="register.php">
            <div class="container">
                <h1>New Database Settings</h1>
                <hr>

                <label for="login"><b>Login (optional)</b></label>
                <input type="text" placeholder="Enter login" name="log" optional>
                
                <label for="psw"><b>Password (optional)</b></label>
                <input type="password" placeholder="Enter password" name="psw" optional>

                <label for="addr"><b>Website address (required)</b></label>
                <input type="url" placeholder="Enter address" name="addr" required>

                <label for="port"><b>Port (required)</b></label>
                <input type="number" min="0" max="65535" placeholder="Enter port" name="port" required>

                <label for="path"><b>Api path (required)</b></label>
                <input type="text" placeholder="Enter path" name="path" required>

                <label for="key"><b>EnOcean box location/key (optional)</b></label>
                <input type="text" placeholder="Enter key" name="key" optional>
   
                <div class="clearfix">
                    <button onclick="window.location.href='main.php'" type="button" class="cancelbtn">Cancel</button>
                    <button type="submit" class="signupbtn" name="reg_db">ADD</button>
                </div>
            </div>
        </form>
    </body>
</html>

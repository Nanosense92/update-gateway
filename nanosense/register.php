<?php include('pushtosql.php') ?>
<!DOCTYPE html>
<html>
<style>
body {font-family: Arial, Helvetica, sans-serif;}
* {box-sizing: border-box}

/* Full-width input fields */
input[type=text], input[type=password] {
  width: 100%;
  padding: 15px;
  margin: 5px 0 22px 0;
  display: inline-block;
  border: none;
  background: #f1f1f1;
}

input[type=text]:focus, input[type=password]:focus {
  background-color: #ddd;
  outline: none;
}

hr {
  border: 1px solid #f1f1f1;
  margin-bottom: 25px;
}

/* Set a style for all buttons */
button {
  background-color: #4CAF50;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  cursor: pointer;
  width: 100%;
  opacity: 0.9;
}

button:hover {
  opacity:1;
}

/* Extra styles for the cancel button */
.cancelbtn {
  padding: 14px 20px;
  background-color: #f44336;
}

/* Float cancel and signup buttons and add an equal width */
.cancelbtn, .signupbtn {
  float: left;
  width: 50%;
}

/* Add padding to container elements */
.container {
  padding: 16px;
}

/* Clear floats */
.clearfix::after {
  content: "";
  clear: both;
  display: table;
}

/* Change styles for cancel button and signup button on extra small screens */
@media screen and (max-width: 300px) {
  .cancelbtn, .signupbtn {
     width: 100%;
  }
}
</style>
<head>
	<title>bdd-reg</title>
	<meta charset="UTF-8">
</head>
<body>

<form method="post" action="register.php" style="border:1px solid #ccc">
  <div class="container">
    <h1>New Database Settings</h1>
    <hr>

   
    <label for="login"><b>login (optional)</b></label>
    <input type="text" placeholder="Enter login" name="log" optional>


  <label for="psw"><b>Password (optional)</b></label>
  <input type="password" placeholder="Enter Password" name="psw" optional>

  <label for="addr"><b>web site address (required)</b></label>
  <input type="text" placeholder="Enter address" name="addr" required>

 <label for="port"><b>port (required)</b></label>
 <input type="text" placeholder="Enter port" name="port" required>

  <label for="path"><b>Api path (required)</b></label>
  <input type="text" placeholder="Enter Path" name="path" required>

  <label for="key"><b>Enocean box location/key (optional)</b></label>
  <input type="text" placeholder="Enter key" name="key" optional>

       
    <div class="clearfix">
      <button onclick="window.location.href='main.php'" type="button" class="cancelbtn">Cancel</button>
      <button type="submit" class="signupbtn" name="reg_db">ADD</button>
    </div>
  </div>
</form>

</body>

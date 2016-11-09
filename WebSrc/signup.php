<?php
require_once('dbconnect.php');
if (isset($_POST["username"])) {
	$sql="select name from Users where name='".$_POST["username"]."'";
	$result = mysql_query( $sql, $conn ) or die( mysql_error() );
	if (mysql_num_rows($result) > 0) {
		$errorString="<h2><center><font color='red'>This name is already in use.</font></center></h2>";
		$errorString.="<h2><center>Please choose another.</center></h2>";
	} else {
		# find the current max stamina
		$sql="select * from Config";
		$result = mysql_query( $sql, $conn ) or die( mysql_error() );
		$staminaMax = mysql_result( $result, 0, "staminaMax" );

		$name=filter_var( $_POST['username'], FILTER_SANITIZE_EMAIL );
		$pword=filter_var( $_POST['password'], FILTER_SANITIZE_EMAIL );

		#create the cuser, give them max stamina
		$sql="insert into Users (name, pword, stamina) values ('$name', '$pword', $staminaMax)";
		if (mysql_query( $sql, $conn ) or die( mysql_error() )) {
			$errorString="<h2><center>Success.  Please <a href='.'>login</a> to play.</center></h2>";
		}
	}
}

?>
<!DOCTYPE html>
<html lang='en'>
<head>
	<link rel="stylesheet" href="css/bootstrap.css">
	<link rel="stylesheet" href="css/currency.css">
	<script src="js/bootstrap.min.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Currency Simulator</title>
</head>
<body onload=document.login.username.focus()>
<div class="container-fluid">
<div class="row head">
<div class="col-md-6 col-md-offset-3">
<h1><center>Currency Simulator sign up</center></h1>
<?=$errorString?>
</div> <!-- col -->
</div> <!-- row head -->
<div class="row">
<div class="col-sm-4 col-sm-offset-2">
<div class="row"><div class="col-md-12">
Please create a username and password.<br/>
I'm not securing the passwords, they are sent in the clear.<br/>
Use the simplest password you want (or none).<br/>
Try to hack your friends, earn currency for them.
</div></div> <!-- row -->
</div> <!-- col -->
<div class="col-sm-4">
<form name=signup action="signup.php" method="post">
<div class="row">
<div class="col-sm-3">Username:</div><div class="col-sm-9"><input type="text" name="username"></div>
</div> <!-- row -->
<div class="row">
<div class="col-sm-3">Password:</div><div class="col-sm-9"><input type="password" name="password"></div>
</div> <!-- row -->
<div class="row">
<div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="Sign Up"></div>
</div> <!-- row -->

</div> <!-- row -->

</div> <!-- container -->

</body>
</html>


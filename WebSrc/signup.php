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

<html><head><title>Currency Simulator</title></head>
	<body onload=document.login.username.focus()>
		<h1><center>Currency Simulator sign up</center></h1>
		<?=$errorString?>
		<hr>
		<table border=0 align=left width=75%>
			<tr><td align=left><table border=0> <!-- Left side menu bar -->
				<tr><td>Please create a username and password.</td></tr>
				<tr><td>I'm not securing the passwords, they are sent in the clear.</td></tr>
				<tr><td>Use the simplist password you want (or none).</td></tr>
				<tr><td>Try to hack your friends, earn currency for them.</td></tr>
			</table>
			<td align=right><table border=0>	<!-- Login Form -->
				<form name=signup action="signup.php" method="post">
					<tr><th>Username:</th><td><input type="text" name="username"></td></tr>
					<tr><th>Password:</th><td><input type="password" name="password"></td></tr>
					<tr><td></td><td align=center><input type="submit" name="submit" value="Sign Up"></td></tr>
				</form>
			</table>
		</table>

	</body>
</html>


<?php
if (!$_COOKIE['userid']) {
	header("Location: .");
	exit;
}
$userid = intval($_COOKIE['userid']);
require_once('dbconnect.php');

#verify user is admin
$sql="select isadmin from Users where id='$userid'";
$result = mysql_query( $sql, $conn ) or die( mysql_error() );
if (mysql_result( $result, 0, "isadmin" ) != 1) {
	header("Location: .");
	exit;
}

# user Info
$editUserFields = array(  # field, text, edit?, show?
	array("name","text","User Name",1,1), 
	array("pword","password","Password",1,0), 
	array("stamina","text","Stamina",1,1),
	array("last","text","Last Used",0,1)
);
$euArray = array();
$sql="select * from Users order by id";
$result = mysql_query( $sql, $conn ) or die( mysql_error() );

$euArray[]="<form name=useredit action='admin.php' method='post'><input type=hidden name=form value=user>";
$euArray[]="<table><tr><th/>";
foreach( $editUserFields as $field ) {
	$euArray[]="<th>$field[2]</th>";
}
$euArray[]="</tr>";

while ($lineArray = mysql_fetch_array($result)) {
	$userid = $lineArray["id"];
	$euArray[]="<tr><td align='center'><input type='radio' name='userid' value='$userid'></td>";
	foreach( $editUserFields as $field ) {
		$fname = $field[0]."_".$userid;
		$displayVal = ($field[4]==1 ? $lineArray[$field[0]] : "");
		if ($field[3] == 1) { # make this an edit field if can edit
			$type=$field[1];
			$displayVal="<input type='$type' name='$fname' value='$displayVal'>";
		}
		$euArray[]="<td>$displayVal</td>";
	}
	$euArray[]="</tr>";
}
$euArray[]="</table><input type=submit value='Update users'></form>";
$editUserHTML=implode("\n", $euArray);
#  don't update timestamp
# UPDATE table SET x=y, timestampColumn=timestampColumn WHERE a=b;

# currency Edit
$editCurrencyFields = array(
	array("name","text","Currency Name",1,1),
	array("type","text","Type",1,1),
	array("level","text","Level",1,1),
	array("cost","text","Cost for next Level",1,1)
);

$cArray = array();
$sql="select * from Currencies order by id, type";
$result = mysql_query( $sql, $conn ) or die( mysql_error() );

$cArray[]="<form name=currencyedit action='admin.php' method='post'><input type=hidden name=form value=currency>";
$cArray[]="<table>";
foreach( $editCurrencyFields as $field ) {
	$cArray[]="<th>$field[2]</th>";
}
$cArray[]="</tr>";

while ($lineArray = mysql_fetch_array($result)) {
	$currencyid = $lineArray["id"];
	$cArray[]="<tr>";
	foreach( $editCurrencyFields as $field ) {
		$fname = $field[0]."_".$currencyid;
		$displayVal = ($field[4]==1 ? $lineArray[$field[0]] : "");
		if ($field[3] == 1) {
			$type=$field[1];
			$displayVal="<input type='$type' name='$fname' value='$displayVal'>";
		}
		$cArray[]="<td>$displayVal</td>";
	}
	$cArray[]="</tr>";
}
$cArray[]="<tr>";
$currencyid++;
foreach( $editCurrencyFields as $field ) {
	$fname = $field[0]."_".$currencyid;
	$cArray[]="<td><input type='$field[1]' name='$fname'></td>";
}
$cArray[]="</tr>";
$cArray[]="</table><input type=submit value='Update Currencies'></form>";
$editCurrencyHTML=implode("\n", $cArray);

#var_dump($_POST);
if (isset($_POST["form"])) {
	if ($_POST["form"]=="currency") {
		$newCurrencyID = (count($_POST)-1)/count($editCurrencyFields);
		for ($lcv = $newCurrencyID; $lcv>0; $lcv--) {
			$fields=array();
			$fields['id'] = $lcv;
			foreach( $editCurrencyFields as $field ) {
				$fields[$field[0]] = $_POST[$field[0]."_".$lcv];
				

			}
			var_dump($fields);
		}
	}
}


?>
<html><head><title>Currency Simulator: Admin</title>
</head>
<body>
<?=$editUserHTML?>
<?=$editCurrencyHTML?>
</body>
</html>

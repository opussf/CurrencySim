<?php

#########################################
# Logs a user on for a day.  Sets cookies
# for the information.
#########################################


#if ((!$_POST[username]) || (!$_POST[password])) {
if ((!$_POST[username])) {
    header("Location: index.html");
    exit;
}
// connect to server and select database
require_once('dbconnect.php');

$name=filter_var( $_POST['username'], FILTER_SANITIZE_EMAIL );
$pword=filter_var( $_POST['password'], FILTER_SANITIZE_EMAIL );

$sql = "select id from Users where name = '$name' and pword = '$pword'";
$result = mysql_query($sql,$conn) or die(mysql_error());

if (mysql_num_rows($result) == 1) {
    $id = mysql_result($result, 0, 'id');
    // set cookie
    setcookie("userid", "$id", 0, "/", "zz9-za.com", 0);
    setcookie("userid", "$id", time()+(1*60*60), "/", "zz9-za.com", 0);  # 1 hour
    //
    header("Location: main.php");
    //$msg =  "<p>$_POST[username] is authorized!</p>";

} else {
    header("Location: index.html");
    exit;
}
?>

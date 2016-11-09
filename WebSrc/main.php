<?php
if (!$_COOKIE["userid"]) {
    header("Location: index.html");
    exit;
}
error_reporting(E_ALL);
$userid = $_COOKIE["userid"];
require_once('dbconnect.php');
$extraHeadTags="";
$adminPage="admin.php";

function makeRandom($count) {
	$tries = 5;
	$sum = 0;
	for( $lcv = 0; $lcv <= $tries; $lcv++ ) {
		$sum += mt_rand(0,1000);
	}
	return( $sum % $count );
}

function addNToCurrencyID($n, $currencyid) {
	global $userid, $conn;
	$sql="select number from UserCurrencies where currencyid='$currencyid' and userid='$userid'";
	
	$result = mysql_query( $sql, $conn ) or die( mysql_error() );
	if (mysql_num_rows($result) == 0) { 
		$sql="insert into UserCurrencies (userid, currencyid, number) values ($userid, $currencyid, $n)";
	} else {
		$n += mysql_result( $result, 0, "number" );
		$sql="update UserCurrencies set number=$n where currencyid='$currencyid' and userid='$userid'";
	}
	$result = mysql_query( $sql, $conn ) or die( mysql_error() );
} 

$sql="select * from Config";
$result = mysql_query( $sql, $conn ) or die( mysql_error() );
$staminaMax = mysql_result( $result, 0, "staminaMax" );
$staminaRate = mysql_result( $result, 0, "staminaGainSeconds" );

$now = time();

$sql = "select name, stamina, UNIX_TIMESTAMP(last) last, isadmin from Users where id='$userid'";
$result = mysql_query($sql, $conn) or die(mysql_error());
$name = mysql_result( $result, 0, "name" );
$oldStamina = mysql_result( $result, 0, "stamina" );
$last = mysql_result( $result, 0, "last" );
$isAdmin = mysql_result( $result, 0, "isadmin" );
$next = 0;
$elapsed = $now - $last;

$staminaGained = floor( $elapsed / $staminaRate );
$timeSinceLast = ($elapsed - ($staminaGained * $staminaRate));
$timePercent = ($timeSinceLast / $staminaRate) * 100;
$timeTilNext = $staminaRate - $timeSinceLast;
$stamina = $oldStamina;
$timeString = "Time till next: ".date("i:s", $timeTilNext);

if ($oldStamina < $staminaMax) { # Add stamina
	$stamina = $oldStamina + $staminaGained;
	$next = $last + floor( $staminaGained * $staminaRate);
	if ($stamina >= $staminaMax) {
		$stamina = $staminaMax;
		$next = $now;
		$timeString = "";
		$timePercent = 0;
	} else {
		$extraHeadTags.="<meta http-equiv='refresh' content='20'></meta>";
	}
}
$staminaPercent = min(100, $stamina / $staminaMax * 100);
	
#print("Updated: $last Old stamina: $oldStamina. Elapsed: $elapsed. tilNext: $timeTilNext Gained: $staminaGained Current: $stamina<br />\n");
#print("Next:    $next Percent: $timePercent<br/>\n");

if (isset($_POST["form"])) {
  if ($_POST["form"]=="roll") {
    
    if ($stamina > 0) { # if you have stamina, decrement it
	$stamina -= 1;
	$next = date("Y-m-d H:i:s", $next);
	$sql="update Users set stamina=$stamina, last='$next' where id='$userid'";
	$result0 = mysql_query($sql, $conn) or die(mysql_error());
	# find some 1 cost currency ids
	$possibleCurrencies = array();
	$sql="select * from Currencies where level=1";
	$result = mysql_query($sql, $conn) or die(mysql_error());
	for( $i = 0; $i < mysql_num_rows($result); $i++ ) {
	  $possibleCurrencies[] = mysql_result($result, $i, "id");
	}
	#var_dump($possibleCurrencies);
	$currencyCount = count($possibleCurrencies);
	$currencyIndex = makeRandom($currencyCount);
	$currencyNew = makeRandom(4) + 1;
	$choosenCurrencyID = $possibleCurrencies[$currencyIndex];

        addNToCurrencyID($currencyNew, $choosenCurrencyID);
	
	#print( "currencyCount: $currencyCount  pick: $choosenCurrencyID count: $currencyNew<br/>\n");
	#print("<br />");
	header("Location: main.php");
	exit;
    }
  }
  if ($_POST["form"]=="buy") {
    $currencyid = intval($_POST["currencyid"]);
    $level = intval($_POST["level"]);

    $sql="select cur.cost cost, cur.type type, fut.id id, fut.type ";
    $sql.="from Currencies cur, Currencies fut ";
    $sql.="where cur.type=fut.type and cur.id='$currencyid' and fut.level='".($level+1)."'";
    $result = mysql_query( $sql, $conn ) or die( mysql_error() );
    $resultArray = mysql_fetch_array( $result );

    addNToCurrencyID( 1, $resultArray["id"] );
    addNToCurrencyID( -$resultArray["cost"], $currencyid );
    header("Location: main.php");
    exit;
    
  }
}

$sql="select id, name, level, number, cost, type from Currencies, UserCurrencies ";
$sql .= "where currencyid=id and userid='$userid' ";
$sql .= "order by type, level";
$result = mysql_query( $sql, $conn ) or die( mysql_error() );
$currencyCounts = array();
for( $i = 0; $i < mysql_num_rows( $result ); $i++ ) {
	$currencyCounts[$i] = array( "name" => mysql_result( $result, $i, "name" ),
		"level" => mysql_result( $result, $i, "level" ),
		"number" => mysql_result( $result, $i, "number" ),
		"cost" => mysql_result( $result, $i, "cost" ),
		"type" => mysql_result( $result, $i, "type" ),
		"id" => mysql_result( $result, $i, "id" )
	 );
}

if ($isAdmin != 0) {
	$name = "<a href='$adminPage'>$name</a>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Currency Simulator</title>
<link rel="stylesheet" href="css/bootstrap.css">
<link rel="stylesheet" href="css/currency.css">
<script src="js/bootstrap.min.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?=$extraHeadTags?>
</head>
<body>
<div class="container-fluid">
<div class="row head">
<div class="row">
<div class="col-lg-6 col-lg-offset-3">
<?php
print("Hello $name.");
?>
</div> <!-- col -->
</div> <!-- row -->
<div class="row">
<div class="col-lg-6">
<div class="meter-wrap" style="border-bottom-style: none;">
<div class="meter-value" style="width: <?=$staminaPercent?>%">
<div class="meter-text">
<?php
print("Energy: $stamina / $staminaMax. $timeString");
?>
</div> <!-- meter-text -->
</div> <!-- meter-value -->
</div> <!-- meter-wrap -->
</div> <!-- col -->
</div>  <!-- row -->
<div class="row">
<div class="col-lg-6">
<div class="meter-wrap" style="border-top-style: none;" >
<div class="meter-value" style="height: 3px; background-color: #f4424e; width: <?=$timePercent?>%">
</div> <!-- meter-value -->
</div> <!-- meter wrap -->
</div> <!-- col -->
</div> <!-- row -->
</div>  <!-- row head -->
<div class="row">
<div class="col-lg-12 amazing-form">
<form action="main.php" method="post">
<input type=hidden name=form value="roll">
--&gt;
<input type=submit value="Do something Amazing">
</form>
</div> <!-- col amazing-form -->
</div> <!-- row -->
<div class="row data-row"> <!-- body of data -->
<div class="col-lg-12">

<?php
# Wrap the list into groups. Array of structure, key is Type
$currencyGroups = array();
foreach( $currencyCounts as $value ) {
	$type = $value["type"];
	if (! array_key_exists($type, $currencyGroups)) {
		$currencyGroups[$type] = array();
	}
	$currencyGroups[$type][] = $value;
}
$counter = 0;
foreach( $currencyGroups as $type => $cGroup ) {
	if ($counter % 4 == 0) {
		print("<div class='row data-group-row'>");
	}
	print <<<END
<div class='col-sm-3 data-group-col'>
<div class='row data-head-row'>
<div class='col-xs-4 table-head'>Currency</div>
<div class='col-xs-4 table-head'>Number</div>
</div> <!-- data-head-row -->

END;

	foreach( $cGroup as $value ) {
		print("<div class='row data-row'>");
		print("<div class='col-xs-4'>({$value['level']}) {$value['name']}</div>");
		print("<div class='col-xs-4'>{$value['number']}</div>");
		if ($value["cost"] > 0 and $value["number"] >= $value["cost"]) {
			print("<div class='col-lg-4'><form action='main.php' method='post'>");
			print("<input type=hidden name=form value='buy'>");
			print("<input type=hidden name=currencyid value='".$value["id"]."'>");
			print("<input type=hidden name=level value='".$value["level"]."'>");
			print("<input type=submit value='Buy Next'></form></div>");
		}
		print("</div> <!-- data-row -->\n");	
	}
	print("</div> <!-- data-group-col -->");
	
	$counter++;
	if ($counter % 4 == 0) {
		print("</div> <!-- data-group-row -->\n");
	} 
}

?>

</div> <!-- main col -->
</div> <!-- row - body of data -->
</div>   <!-- container-fluid -->
</body>
</html>

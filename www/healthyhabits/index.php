<?php
header('Content-Type: text/html; charset=utf-8');
//require '../kint/Kint.class.php';
// http://raveren.github.io/kint/
// d() - Dump
// dd() - Dump & Die
// d(1) - trace
// !d() - expanded
$time_start = microtime(true);
define('eol',PHP_EOL);
?>

<html>
<head>
	<title>Healthy Habits Jar</title>
		<link rel="apple-touch-icon" href="jar.jpg">
		<link rel="icon" href="jar.jpg">	<style>
		body {width:500px;}
		#header {position:relative;margin:5px auto 0;z-index:10;margin:5px auto;text-align:center;}
		#jar {position:absolute;top:10px;left:0;z-index:-10;width:500px;height:500px;}
		#list {position:absolute;width:280px;top:170px;left:110px;z-index:10;}
		.type {text-decoration:underline;margin:0 0 5px 0;}
		.result {margin:50px auto;width:280px;text-align:center;font-size:16px;font-weight:bold;}
		#footer {position:absolute;top:490px;left:0;width:500px;z-index:10;text-align:center;font-size:12px;}
	</style>
</head>
<body>
	<div id="header"><input type="button" name="refresh" value="Reach into the Healthy Habits Jar" onClick="document.location.reload();return false"/></div>
	<img src="jar.jpg" id="jar">
	<div id="list">

<?php
$conn = connect();
getRandom();

$time_end = microtime(true);
$time = round($time_end - $time_start,3);
echo "</div>".eol;
//echo "<div id='timer'>$time seconds</p>" . eol;
echo "<div id='footer'>";
getStats();
echo "</div>";
echo "</body></html>" .eol;
$conn->close();


function getRandom(){
	Global $conn;
	//echo $q;

	$sql = "(select value from activities ORDER BY RAND() LIMIT 1) union (select value from foods ORDER BY RAND() LIMIT 1)";
	//echo $sql;
	$rs = $conn->query($sql);
	if (!$rs) {
		echo "Could not execute query: $sql" . eol;
	}
	if ($rs->num_rows == 0){
		echo 'No Results Found' . eol;
	}
	$type = 'Activity';
	while ($row = $rs->fetch_assoc()) {
		echo "<div class='result'><div class='type'>$type</div>{$row['value']}</div>".eol;
		$type = 'Food';
	}
	$rs->free_result();
}

function getStats(){
	Global $conn;

	$sql = "(select count(*) as total from activities) union (select count(*) as total from foods)";
	//echo $sql;
	$rs = $conn->query($sql);
	if (!$rs) {
		echo "Could not execute query: $sql" . eol;
	}
	if ($rs->num_rows == 0){
		echo 'No Results Found' . eol;
	}
	$type = 'Activities';
	while ($row = $rs->fetch_assoc()) {
		echo "There are {$row['total']} $type in the jar<br/>".eol;
		$type = 'Foods';
	}
	$rs->free_result();
	$conn->close();
}

function connect(){
	$host = 'db'; 
	$user = 'root'; 
	$pass = 'MYponydog1!!!'; 
	$db = 'healthyhabits';

	$conn = new mysqli($host,$user,$pass,$db);

	if ($conn->connect_errno) {
		echo "Failed to connect to MySQL: (" . $conn->connect_errno . ") " . $conn->connect_error;
	}
	$conn->set_charset('utf8mb4');
	return $conn;
}
?>
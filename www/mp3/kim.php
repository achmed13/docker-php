<?php
    @apache_setenv('no-gzip', 1);
    @ini_set('zlib.output_compression', 0);
    @ini_set('implicit_flush', 1);
header('Content-Type: text/html; charset=utf-8');
$time_start = microtime(true);
define('eol',PHP_EOL);
?>

<html>
<head>
	<title>Random Playlist</title>
	<link href='style.css' rel='stylesheet' type='text/css'>
</head>
<body>

<?php

$time_end = microtime(true);
$time = round($time_end - $time_start,3);
$num = 50;
if(isset($_GET['num'])){
	$num = $_GET['num'];
}
echo "<form method='get' action='' id='searchform'>Give me <input type='text' name='num' value='$num' size=3> Songs <input type='submit' name='pl' value='Get New Playlist'>&nbsp;<a href='playlist.m3u'>Download this Playlist for iTunes</a>&nbsp;<a href='KimRandom.m3u'>Download Playlist for Subsonic</a>&nbsp;<a href='stream.m3u'>Listen to this Playlist Now</a></form><hr>" . eol;
if(isset($_GET['pl'])){
	getPlaylist(1,$num);
} else {
	getPlaylist(0,$num);
}
echo "<hr><div class='timer'>$time seconds</p>" . eol;
echo "</body></html>" .eol;

function getPlaylist($new,$num){
	$r = connect();
	$q = $conn->real_escape_string($q);
	if($new==1){
		$sql = "call getRandoms($num);";
	} else {
		$sql = "select * from playlist;";
	}
	$rs = $conn->query($sql);
	if (!$rs) {
		echo "Could not execute query: $sql" . eol;
	}
	if ($rs->num_rows == 0){
		echo 'No Results Found' . eol;
	}
	
	$playlist = fopen("playlist.m3u", "w") or die("Unable to open file!");
	$subsonic = fopen("KimRandom.m3u", "w") or die("Unable to open file!");
	$stream = fopen("stream.m3u", "w") or die("Unable to open file!");
	while ($row = $rs->fetch_assoc()) {
		$pl = "//shuttle/media/albums".$row['path'];
		$ss = "m:/albums".$row['path'];
		$st = "http://seanloos.com/music/".rawurlencode($row['artist'])."/".rawurlencode($row['album'])."/".rawurlencode($row['song']);
		echo "<a href='$st' target='mp3'>{$row['artist']} - {$row['album']} - {$row['song']}</a><br/>".eol;
		fwrite($playlist, $pl.eol);
		fwrite($subsonic, $ss.eol);
		fwrite($stream, $st.eol);
	}
	fclose($playlist);
	fclose($stream);
	$rs->free_result();
	$conn->close();
}

function connect(){
	$host = 'db'; 
	$user = 'root'; 
	$pass = 'MYponydog1!!!'; 
	$db = 'music';

	$conn = new mysqli($host,$user,$pass,$db);

	if ($conn->connect_errno) {
		echo "Failed to connect to MySQL: (" . $conn->connect_errno . ") " . $conn->connect_error;
	}
	$conn->set_charset('utf8mb4');
	return $conn;
}
?>
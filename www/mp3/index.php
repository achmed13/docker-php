<?php
    @apache_setenv('no-gzip', 1);
    @ini_set('max_execution_time', 120);
    @ini_set('zlib.output_compression', 0);
    @ini_set('implicit_flush', 1);
header('Content-Type: text/html; charset=utf-8');
$time_start = microtime(true);
define('eol',PHP_EOL);
?>

<html>
<head>
	<title>MP3 Search</title>
	<link href='style.css' rel='stylesheet' type='text/css'>
</head>
<body>

<?php
$q	= (isset($_GET['q'])) ? $_GET['q'] : '';
showSearch($q);
getSummary($q);
if(isset($_GET['update'])){
	updateDB();
} else {
	getResults($q);
}

$time_end = microtime(true);
$time = round($time_end - $time_start,3);
echo "<div class='timer'>$time seconds</p>" . eol;
echo "</body></html>" .eol;

function showSearch($q){
	echo "<form method='get' action='' id='searchform'>" . eol;
	$q = (empty($q)) ? '' : $q;
	echo "<input type='text' id='q' name='q' value='$q'> <input type='submit' name='search' value='Search'> <input type='submit' name='update' value='Update'>" . eol;
	echo " <a href='kim.php'>Kim's Playlists</a>".eol;
	echo "</form>" . eol;
	echo "<hr/>" . eol;
	// sleep(1);
	// echo str_pad("",4096);
	// sleep(1);
}

function getSummary($q){
	$conn = connect();
	$q = $conn->real_escape_string($q);
	$sql = "select count(distinct artist) as artists, count(0) as songs from songs where path like '%$q%'";
	//echo $sql;
	$rs = $conn->query($sql);

	while ($row = $rs->fetch_assoc()) {
		$hue = getHue($row['album']);
		echo "<div>{$row['artists']} artists - {$row['songs']} songs</div>".eol;
	}
	$rs->free_result();
	$conn->close();

}

function getResults($q){
	if (strlen($q)<3){
		echo "Please enter at least 3 characters to search." . eol;
		return;
	}

	$conn = connect();
	//echo $q;
	$q = $conn->real_escape_string($q);
	$sql = "select * from songs where path like '%$q%' order by artist,album";
	//echo $sql;
	$rs = $conn->query($sql);
	if (!$rs) {
		echo "Could not execute query: $sql" . eol;
	}
	if ($rs->num_rows == 0){
		echo 'No Results Found' . eol;
	}
	
	while ($row = $rs->fetch_assoc()) {
		$hue = getHue($row['album']);
		echo "<div class='result' style='background-color:hsl($hue,100%,80%);'><a href='/music/".rawurlencode($row['artist'])."/".rawurlencode($row['album'])."/".rawurlencode($row['song'])."' target='mp3'>{$row['artist']} - {$row['album']} - {$row['song']}</a></div>".eol;
	}
	$rs->free_result();
	$conn->close();
}

function updateDB(){
	$dir = '/var/www/html/music';
	$allowext = array("mp3");
	list_directory($dir,$allowext);
}

function rstrstr($haystack,$needle) {
	return substr($haystack, 0,strrpos($haystack, $needle));
}

function list_directory($dir,$allowext) {
   $file_list = array();
   $stack[] = $dir;
	echo str_pad("Updating<br>",4096);
	$conn = connect();
	$sql = "truncate table songs;";
	$rs = $conn->query($sql);
	if (!$rs) {
		echo "Could not execute query: $sql" . eol;
		die();
	}
	$i=0;
   while ($stack) {
       $current_dir = array_pop($stack);
       if ($dh = opendir($current_dir)) {
           while (($file = readdir($dh)) !== false) {
               if ($file !== '.' AND $file !== '..') {
					$current_file = "{$current_dir}/{$file}";
					$ext = substr($file, strrpos($file, '.') + 1);
					$report = array();
                   if (is_file($current_file) && in_array($ext,$allowext)) {
						$f = "{$current_dir}/{$file}";
						//$file_list[] = "{$f}";
						$f = $conn->real_escape_string(utf8_encode($f));
						$p = substr($f,strrpos($f,'albums/') + 6);
						$s = substr($f, strrpos($f, '/') + 1);
						$f = rstrstr($f,'/');
						$al = substr($f, strrpos($f, '/') + 1);
						$f = rstrstr($f,'/');
						$ar = substr($f, strrpos($f, '/') + 1);
						$shardSize=200;
						if ($i % $shardSize == 0) {
							if ($i != 0) {
								$rs = $conn->query("insert into songs (artist,album,song,path) values ".implode(",",$sql));
								if (!$rs) {
									echo "<span style='float:left;color:red;'>E</span><br>".eol;
									d($sql);
									//trigger_error(mysql_error(), E_USER_ERROR); 
									die();
								}
							}
							set_time_limit(30);
							echo "<span style='float:left;'>.</span>".eol;
							$sql = array();
						}
						$sql[] = "('".$ar."', '".$al."','".$s."','".$p."')";
						$i++;
                   } elseif (is_dir($current_file)) {
                       $stack[] = $current_file;
                       //$file_list[] = "{$current_dir}/{$file}/";
                   }
               }
           }
       }
}
	$conn->close();
	//return $file_list;
	return;
}

function getHue($str){
	$h=0;
	$i=1;
	If(!empty($str)){
		$str = strtoupper($str);
		for($i=1;$i<=strlen($str);$i++){
			$h += ord(substr($str,$i,1));
		}
		$h = $h % 360;
	}
	else {
		$h = 0;
	}
	return $h;
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